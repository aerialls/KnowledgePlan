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

    protected $flowsAccepted = 0;

    protected $flowsRejected = 0;

    public function createSimulation($filename)
    {
        $this->simulation = new Simulation();
        $file = new \SplFileObject($filename);

        foreach($file as $line) {
            $line = str_replace(array("\r\n", "\n", "\r"), '', $line);

            // New event
            if ('at' === substr($line, 0, 2) && false === strpos($line, 'FLOW ID')) {
                $values = $this->explodeLine($line);

                $this->addPoint($values);
                $this->addInformations($values);
            } else if (false !== strpos($line, 'Admit a new flow')) {
                $this->flowsAccepted++;
            } else if (false !== strpos($line, 'REJECT')) {
                $this->flowsRejected++;
                $this->flowsAccepted--;
            }
        }

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
            list($sec) = explode(' (ms)', $array['waiting_time']);
            $array['waiting_time'] = $sec;
        }

        return $array;
    }

    private function addPoint(array $values)
    {
        if (isset($values[self::X_NAME]) && isset($values[self::Y_NAME])) {
            $this->points[] = array($values[self::X_NAME], $values[self::Y_NAME]);
        }

        if (self::NBR_POINTS === count($this->points)) {
            $this->simulation->addPlot($values['time'], $this->points);
            $this->points = array();
        }
    }

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