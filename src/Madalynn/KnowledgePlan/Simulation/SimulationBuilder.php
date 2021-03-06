<?php

/*
 * This file is part of the KnowledgePlan website.
 *
 * (c) 2012 Julien Brochet <mewt@madalynn.eu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Madalynn\KnowledgePlan\Simulation;

class SimulationBuilder
{
    /**
     * Options (like axis..)
     *
     * @var array $options
     */
    protected $options;

    /**
     * Options for a specific simulation
     *
     * @var array $localOptions
     */
    protected $localOptions;

    /**
     * @var Madalynn\KnowledgePlan\Simulation $simulation
     */
    protected $simulation;

    /**
     * @var array $points
     */
    protected $points = array();

    /**
     * @var array $centroids
     */
    protected $centroids = array();

    /**
     * @var array $hlm
     */
    protected $hlm = array();

    protected $currentTime;

    protected $flowsAccepted;

    protected $flowsRejected;

    /**
     * @var array $values
     */
    protected $values;

    /**
     * @var array $averages
     */
    protected $averages;

    /**
     * Informations about the last HLM Queue
     *
     * @var array $knowledgePlane
     */
    protected $knowledgePlane;

    /**
     * Constructor
     *
     * @param array $options      Options
     * @param array $localOptions Options for a specific simulation
     */
    public function __construct(array $options = array(), array $localOptions = array())
    {
        $defaultOptions = array(
            'x_name'    => 'outputrate',
            'x_min'     => 0,
            'x_max'     => 6,
            'y_name'    => 'delay',
            'y_min'     => 0,
            'y_max'     => 60,
            'delay_max' => 10
        );

        $options = array_merge($defaultOptions, $options);

        // Check for labels
        if (!isset($options['x_label'])) {
            $options['x_label'] = $this->reverseUnderscore($options['x_name']);
        }

        if (!isset($options['y_label'])) {
            $options['y_label'] = $this->reverseUnderscore($options['y_name']);
        }

        $this->options      = $options;
        $this->localOptions = $localOptions;
    }

    private function reset()
    {
        $this->flowsAccepted      = 0;
        $this->flowsRejected      = 0;
        $this->currentTime        = 0;

        $this->values = array(
            'outputrate'           => array(),
            'delay'                => array(),
            'timeslot_with_qos'    => 0,
            'timeslot_without_qos' => 0
        );

        $this->averages = array(
            'outputrate'           => 0,
            'delay'                => 0,
            'timeslot_with_qos'    => 100,
            'timeslot_without_qos' => 0,
        );

        $this->knowledgePlane = array(
            'type' => 'Unknown',
            'mu'   => 0,
            'cv'   => 0,
            'k'    => null,
            'off'  => 0
        );

        $this->points      = array();
        $this->centroids   = array();
        $this->hlm         = array();
    }

    public function create($filename, array $options = array())
    {
        $this->reset();
        $options = array_merge($this->options, $options);

        // Can throw an exception if the file is not found
        $file = new \SplFileObject($filename);

        $this->simulation = new Simulation($file->getFilename());

        $lastTime = -1;
        $step = 0;

        foreach ($file as $line) {
            $line = str_replace(array("\r\n", "\n", "\r"), '', $line);

            // New event
            if ('at' === substr($line, 0, 2) && false === strpos($line, 'FLOW ID')) {
                $values = $this->explodeInformationsLine($line);

                $this->currentTime = $values['time'];

                // Update the outputrate average
                if (isset($values['outputrate'])) {
                    $this->values['outputrate'][] = $values['outputrate'];
                }

                // Update the delay average
                if (isset($values['delay'])) {
                    $delay    = $values['delay'];
                    $maxDelay = $this->options['delay_max'];

                    $this->values['delay'][] = $delay;
                    if ($delay > $maxDelay) {
                        $this->values['timeslot_without_qos']++;
                    } else {
                        $this->values['timeslot_with_qos']++;
                    }
                }

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
            } elseif (0 === strpos($line, 'KmeansCentroids')) {
                // Centroids points
                $this->centroids = $this->explodePointsLine($line);
            } elseif (0 === strpos($line, 'QUEUE_HLM')) {
                // Queue HLM
                $this->hlm = $this->explodePointsLine($line);
                // We have all the informations needed to create
                // the next plot for the simulation
                $this->addCurrentPlot();
            } elseif (false !== strpos($line, 'ACCEPT')) {
                $this->flowsAccepted++;
            } elseif (false !== strpos($line, 'REJECT')) {
                $this->flowsRejected++;
            } elseif (0 === strpos($line, 'refDelay')) {
                // New delay max
                $delayMax = substr($line, 11, 13 - strlen($line));
                $this->options['delay_max'] = $delayMax;
            } elseif (0 === strpos($line, 'refQueue')) {
                // M/G/1 definition
                $hlm   = array();
                $parts = explode(' ', $line);

                $hlm['mu'] = $parts[4];
                $hlm['cv'] = $parts[5];

                if ($parts[2] == 34) {
                    $hlm['type'] = 'M/G/1';
                    $hlm['off'] = $parts[6];
                    $hlm['k'] = null;
                } else {
                    $hlm['type'] = 'M/G/1/k';
                    $hlm['k'] = $parts[6];
                    $hlm['off'] = $parts[7];
                }

                $this->knowledgePlane = $hlm;
            }
        }

        // Add an empty plot at the start
        $minTime = $this->simulation->getMinTime();
        $this->simulation->addPlot($minTime, array(
            'points'    => array(),
            'centroids' => array(),
            'hlm'       => array()
        ));

        $this->simulation->setOptions($this->getSpecificOptions($file->getFilename()));

        return $this->simulation;
    }

    /**
     * Explode a line from the line to an array of data
     *
     * Example:
     *
     * QUEUE_HLM [0.507101 1.06674 6.95322e-310] [0.517432 1.07231 6.95322e-310]
     * [0.539682 1.08442 6.95322e-310] [0.729238 1.1934 6.95322e-310]
     * [0.747203 1.2043 6.95322e-310]
     *
     * @param string $line The line
     *
     * @return array
     */
    private function explodePointsLine($line)
    {
        $numberRegex = '[-+]?[0-9]*\.?[0-9]+([eE][-+]?[0-9]+)?';
        $regex = sprintf('/\[(%s) (%1$s) (%1$s)\]/', $numberRegex);

        $matches = array();
        if (0 === preg_match_all($regex, $line, $matches, PREG_SET_ORDER)) {
            return array();
        }

        $values = array();
        foreach ($matches as $match) {
            $values[] = array($match[1], $match[3]);
        }

        return $values;
    }

    /**
     * Explode an informations line from the file to an array
     * of data
     *
     * Example:
     *
     * at 30.2QUEUE [0, 1] Utilization = 0.658304 Arrivals Rate = 5171101bps
     * std_Arrivals = 5338070bps ar_[2831589, 7510613] Peak Rate = 5171101bps
     * Waiting Time = 8 (ms) 8034470ns std_W_T = 0ns wt_[8034470ns, 8034470ns]
     * Service Time = 651813ns Loss = 0 Link State = G Mean_inter_arrivals_time
     * = 979879ns cv_inter_arrivals_time = 1.04204 OutputRate = 1.00996
     * Delay = 8.68628
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
    private function explodeInformationsLine($line)
    {
        $values = explode("\t", $line);

        $array = array();
        foreach ($values as $key => $value) {
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
     *
     * @param array $values The informations
     */
    private function addPoint(array $values)
    {
        $x = $this->options['x_name'];
        $y = $this->options['y_name'];

        if (isset($values[$x]) && isset($values[$y])) {
            $this->points[] = array($values[$x], $values[$y]);
        }
    }

    /**
     * Add the current plot to the simulation
     */
    private function addCurrentPlot()
    {
        if (0 === count($this->centroids) || 0 === count($this->hlm)) {
            throw new \RuntimeException('Unable to create a new plot if "centroids" or "hlm" are empty.');
        }

        // Same code for outputrage and delay average
        foreach (array('outputrate', 'delay') as $name) {
            $average = 0;
            if (0 !== $length = count($this->values[$name])) {
                $average = array_sum($this->values[$name]) / $length;
            }

            $this->averages[$name] = $average;
        }

        // % for timeslot
        $total = $this->values['timeslot_with_qos'] + $this->values['timeslot_without_qos'];
        $this->averages['timeslot_with_qos'] = ($this->values['timeslot_with_qos'] / $total) * 100;
        $this->averages['timeslot_without_qos'] = ($this->values['timeslot_without_qos'] / $total) * 100;

        $informations = array(
            'points'    => $this->points,
            'centroids' => $this->centroids,
            'hlm'       => $this->hlm,
        );

        $this->simulation->addPlot($this->currentTime, $informations);

        $this->points      = array();
        $this->centroids   = array();
        $this->hlm         = array();

        $this->values = array(
            'outputrate'           => array(),
            'delay'                => array(),
            'timeslot_with_qos'    => 0,
            'timeslot_without_qos' => 0
        );
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
            'flows_accepted'       => $this->flowsAccepted,
            'flows_rejected'       => $this->flowsRejected,
            'outputrate_average'   => $this->averages['outputrate'],
            'delay_average'        => $this->averages['delay'],
            'timeslot_with_qos'    => $this->averages['timeslot_with_qos'],
            'timeslot_without_qos' => $this->averages['timeslot_without_qos'],
            'knowledge_plane'       => $this->knowledgePlane
        );

        // Merge with the default options
        $values = array_merge($informations, $values);

        $this->simulation->addInformations($values['time'], $values);
    }

    private function getSpecificOptions($name)
    {
        if (isset($this->localOptions[$name])) {
            // We have specific options for this simulaiton
            return array_merge($this->options, $this->localOptions[$name]);
        }

        return $this->options;
    }
}
