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

class ExperienceBuilder
{
    protected $manager;

    public function __construct(SimulationManager $manager)
    {
        $this->manager = $manager;
    }

    public function create($name, array $options)
    {
        $options = array_merge(array(
            'title' => 'Knowledge Plan'
        ), $options);

        if (!isset($options['simulations']) || empty($options['simulations'])) {
            throw new \InvalidArgumentException('An Experience must have at least one simulation.');
        }

        $simulations = array();
        foreach ($options['simulations'] as $nameSimulation) {
            try {
                $simulation = $this->manager->get($nameSimulation);
            } catch (\Exception $e) {
                throw new \InvalidArgumentException(sprintf('Unable to find the "%s" simulation: %s', $name, $e->getMessage()));
            }

            $simulations[] = $simulation;
        }

        return new Experience($name, $simulations, $options);
    }
}