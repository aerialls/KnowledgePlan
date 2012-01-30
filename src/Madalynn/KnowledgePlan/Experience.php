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

class Experience
{
    /**
     * @var Madalynn\KnowledgePlan\Simulation\SimulationManager $simulationManager
     */
    protected $simulationManager;

    /**
     * @var array $simulations
     */
    protected $simulations;

    /**
     * Constructor
     *
     * @param array $simulations Simulations. Can be a Simulation instance or
     *                           a path to the simulation output file
     *
     * @param SimulationManager $simulationManager A SimulationManager instance
     *
     * @throws \RuntimeException If the manager is unable to load a simulation
     */
    public function __construct(array $simulations, SimulationManager $simulationManager)
    {
        if (0 === count($simulations)) {
            throw new \InvalidArgumentException('An experience must have at least one simulation.');
        }

        $this->simulationManager = $simulationManager;
        $this->simulations       = array();

        foreach($simulations as $simulation) {
            if ($simulation instanceof Madalynn\KnowledgePlan\Simulation\Simulation) {
                $this->simulations[] = $simulation;
            } else {
                try {
                    $simulation = $simulationManager->get($simulation);
                } catch(\Exception $e) {
                    throw new \RuntimeException(sprintf('Unable to load the simulation "%s": %s', $simulation, $e->getMessage()));
                }
            }
        }
    }

    public function getSimulations()
    {
        return $this->simulations;
    }
}