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

use Madalynn\KnowledgePlan\Simulation\SimulationManager;
use Madalynn\KnowledgePlan\Simulation\Simulation;
use Madalynn\KnowledgePlan\Utils\MathUtils;

class Experience
{
    /**
     * @var Madalynn\KnowledgePlan\Simulation\SimulationManager $manager
     */
    protected $manager;

    /**
     * @var array $simulations
     */
    protected $simulations;

    protected $minTime;

    protected $maxTime;

    protected $step;

    /**
     * The name of the experience
     */
    protected $name;

    public function __construct($name, array $simulations, array $options = array())
    {
        if (0 === count($simulations)) {
            throw new \InvalidArgumentException('An experience must have at least one simulation.');
        }

        $this->simulations = array();
        $this->step        = 1;
        $this->minTime     = INF;
        $this->maxTime     = -INF;
        $this->name        = $name;
        $this->options     = $options;

        // Simulations
        foreach ($simulations as $simulation) {
            $this->addSimulation($simulation);
        }
    }

    /**
     * Add a simulation to the experience
     *
     * @param string $name
     * @param Simulation $simulation
     */
    private function addSimulation(Simulation $simulation)
    {
        // Check for max time and min time and step
        $this->minTime = min($simulation->getMinTime(), $this->minTime);
        $this->maxTime = max($simulation->getMaxTime(), $this->maxTime);
        $this->step    = min($simulation->getStep(), $this->step);

        $this->simulations[$simulation->getName()] = $simulation;
    }

    public function getSimulations()
    {
        return $this->simulations;
    }

    public function getStep()
    {
        return $this->step;
    }

    public function getMinTime()
    {
        return $this->minTime;
    }

    public function getMaxTime()
    {
        return $this->maxTime;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getOptions()
    {
        return $this->options;
    }
}