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

    public function __construct()
    {
        $this->informations = array();
        $this->plots = array();

        $this->step = 1;
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

    public function addPlot($time, array $points)
    {
        $this->plots[strval($time)] = $points;
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

    public static function __set_state($data)
    {
        $simulation = new Simulation();

        $simulation->informations = $data['informations'];
        $simulation->plots = $data['plots'];
        $simulation->minTime = $data['minTime'];
        $simulation->maxTime = $data['maxTime'];
        $simulation->step = $data['step'];

        return $simulation;
    }
}