<?php

/*
 * This file is part of the KnowledgePlan website.
 *
 * (c) 2012 Julien Brochet <mewt@madalynn.eu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Madalynn\KnowledgePlan;

class SimulationBuilder
{
    /**
     * The number of points for a plot
     */
    const NBR_POINTS = 25;

    /**
     * The name of the option for the X axe for the plot
     */
    const X_NAME = 'utilization';

    /**
     * The name of the option for the Y axe for the plot
     */
    const Y_NAME = 'waiting_time';

    /**
     * @var Madalynn\KnowledgePlan\Simulation $simulation
     */
    protected $simulation;

    /**
     * @var array $points
     */
    protected $points = array();

    protected $flowsAccepted;

    protected $flowsRejected;

    public function createSimulation($filename)
    {
        // Reset
        $this->flowsAccepted = 0;
        $this->flowsRejected = 0;
        $this->points = array();
        $this->simulation = new Simulation();

        $file = new \SplFileObject($filename);
        $lastTime = -1;
        $step = 0;

        foreach($file as $line) {
            $line = str_replace(array("\r\n", "\n", "\r"), '', $line);

            // New event
            if ('at' === substr($line, 0, 2) && false === strpos($line, 'FLOW ID')) {
                $values = $this->explodeLine($line);

                $this->addPoint($values);
                $this->addInformations($values);

                // Step between 2 events
                if (0 === $step) {
                    // The $step is usefull to calculate the
                    // step just ONE time and not at every event
                    $currentTime = $values['time'];
                    if (-1 !== $lastTime) {
                        $step = $currentTime - $lastTime;
                        $this->simulation->setStep($step);
                    }

                    $lastTime = $currentTime;
                }
            } else if (false !== strpos($line, 'Admit a new flow')) {
                $this->flowsAccepted++;
            } else if (false !== strpos($line, 'REJECT')) {
                $this->flowsRejected++;
                $this->flowsAccepted--;
            }
        }

        // Add an empty plot at the start
        $this->addPlot($this->simulation->getMinTime());

        return $this->simulation;
    }

    /**
     * Explode a line from the file to an array
     * of data
     *
     * @param string $line The line
     *
     * @return array(
     *   time
     *   utilization
     *   arrivals_rate
     *   std_arrivals
     *   peak_rate
     *   waiting_time
     *   std_w_t
     *   service_time
     *   loss
     *   link_state
     *   mean_inter_arrivals_time
     *   cv_inter_arrivals_time
     * )
     */
    private function explodeLine($line)
    {
        $values = explode("\t", $line);

        $array = array();
        foreach($values as $key => $value) {
            // Time: at [0-9]+
            if (0 === $key) {
                $array['time'] = substr($value, 3);
                continue;
            }

            // QUEUE [0, 1] and other
            if (false === strpos($value, '=')) {
                continue;
            }

            list($k, $v) = explode(' = ', $value);
            $array[strtolower(str_replace(' ', '_', trim($k)))] = trim($v);
        }

        // Modification for the waiting time...
        if (isset($array['waiting_time'])) {
            $list = explode(' ', $array['waiting_time']);
            // The ns time is alway the last element in the waiting
            // time part
            $time = $list[count($list) - 1];
            $array['waiting_time'] = substr($time, 0, count($time) - 3);
        }

        return $array;
    }

    /**
     * Add a point to the latest plot in memory
     * If the number of points is equals to NBR_POINTS,
     * the Simulation::addPlot is called
     *
     * This method is looking for the X_NAME and Y_NAME in
     * the input array to creat a point.
     *
     * @param array $values The informations
     */
    private function addPoint(array $values)
    {
        if (isset($values[self::X_NAME]) && isset($values[self::Y_NAME])) {
            $this->points[] = array($values[self::X_NAME], $values[self::Y_NAME]);
        }

        if (self::NBR_POINTS === count($this->points)) {
            $this->addPlot($values['time'], $this->points);
            $this->points = array();
        }
    }

    /**
     * Add a plot to the simulation
     *
     * @param string $time
     */
    private function addPlot($time, array $points = array())
    {
        $informations = array(
            'time'    => $time,
            'points'  => $points,
            'label_x' => $this->reverseUnderscore(self::X_NAME),
            'label_y' => $this->reverseUnderscore(self::Y_NAME)
        );

        $this->simulation->addPlot($time, $informations);
    }

    private function reverseUnderscore($text)
    {
        return ucfirst(str_replace('_', ' ', $text));
    }

    /**
     * Add informations to the simulation
     *
     * @param array $values
     */
    private function addInformations(array $values)
    {
        $informations = array(
            'time'           => null,
            'utilization'    => null,
            'peak_rate'      => null,
            'waiting_time'   => null,
            'loss'           => null,
            'flows_accepted' => $this->flowsAccepted,
            'flows_rejected' => $this->flowsRejected
        );

        // Merge with the default options
        $values = array_merge($informations, $values);

        $this->simulation->addInformations($values['time'], $values);
    }
}