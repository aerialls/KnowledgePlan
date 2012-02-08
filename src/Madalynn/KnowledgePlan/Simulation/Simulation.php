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

class Simulation
{
    /**
     * @var array $informations
     */
    protected $informations;

    /**
     * @var array $plots
     */
    protected $plots;

    /**
     * Options
     *
     * @var array $options
     */
    protected $options;

    /**
     * The step (in sec)
     *
     * @var mixed $step
     */
    protected $step;

    /**
     * The time for the first entry (in sec)
     */
    protected $minTime;

    /**
     * The time for the last entry (in sec)
     */
    protected $maxTime;

    /**
     * The name of the simulation
     */
    protected $name;

    public function __construct($name)
    {
        $this->options      = array();
        $this->informations = array();
        $this->plots        = array();

        $this->name    = $name;
        $this->step    = 1;
        $this->minTime = INF;
        $this->maxTime = -INF;
    }

    public function addInformations($time, array $informations)
    {
        $this->informations[strval($time)] = $informations;

        // Check for max time and min time
        $this->minTime = min($time, $this->minTime);
        $this->maxTime = max($time, $this->maxTime);
    }

    public function addPlot($time, array $informations)
    {
        // Added the time to the informations
        $informations = array_merge(array(
            'time' => $time
        ), $informations);

        $this->plots[strval($time)] = $informations;
    }

    public function getInformations()
    {
        return $this->informations;
    }

    public function getPlots()
    {
        return $this->plots;
    }

    public function setStep($step)
    {
        $this->step = $step;
    }

    public function getStep()
    {
        return $this->step;
    }

    public function setOptions(array $options)
    {
        $this->options = $options;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function getOption($name)
    {
        return isset($this->options[$name]) ? $this->options[$name] : null;
    }

    /**
     * Returns the min time in the simulation
     */
    public function getMinTime()
    {
        return $this->minTime;
    }

    /**
     * Returns the max time in the simulation
     */
    public function getMaxTime()
    {
        return $this->maxTime;
    }

    public function getName()
    {
        return $this->name;
    }

    public static function __set_state($data)
    {
        $simulation = new Simulation($data['name']);

        $simulation->informations = $data['informations'];
        $simulation->plots        = $data['plots'];
        $simulation->minTime      = $data['minTime'];
        $simulation->maxTime      = $data['maxTime'];
        $simulation->step         = $data['step'];
        $simulation->options      = $data['options'];

        return $simulation;
    }
}