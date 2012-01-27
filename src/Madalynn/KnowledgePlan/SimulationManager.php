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

use Madalynn\KnowledgePlan\Cache\CacheInterface;
use Madalynn\KnowledgePlan\Loader\LoaderInterface;

class SimulationManager
{
    /**
     * @var Madalynn\KnowledgePlan\Cache\CacheInterface $cache
     */
    protected $cache;

    /**
     * @var Madalynn\KnowledgePlan\SimulationBuilder $builder;
     */
    protected $builder;

    public function __construct(SimulationBuilder $builder, CacheInterface $cache)
    {
        $this->cache = $cache;
        $this->builder = $builder;
    }

    /**
     * Returns a simulation
     *
     * If the simulation does not exist yet in the
     * cache, the loader is call to retrieve it
     *
     * @param string  $filename The filename
     * @param Boolean $force    If true, the cache won't be called
     * @param Boolean $store    Store in the cache
     *
     * @return Madalynn\KnowledgePlan\Simulation
     */
    public function get($filename, $force = false, $store = true)
    {
        if (false === $force && $this->cache->has($filename)) {
            return $this->cache->get($filename);
        }

        $simulation = $this->builder->createSimulation($filename);

        // Add a new entry to the cache
        if (true === $store) {
            $this->cache->set($filename, $simulation);
        }

        return $simulation;
    }
}