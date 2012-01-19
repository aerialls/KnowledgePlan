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
     * @var Madalynn\KnowledgePlan\Loader\LoaderInterface $loader;
     */
    protected $loader;

    public function __construct(CacheInterface $cache, LoaderInterface $loader)
    {
        $this->cache = $cache;
        $this->loader = $loader;
    }

    /**
     * Returns a simulation
     *
     * If the simulation does not exist yet in the
     * cache, the loader is call to retrieve it
     *
     * @param string $filename The filename
     *
     * @return Madalynn\KnowledgePlan\Simulation
     */
    public function get($filename)
    {
        if ($this->cache->has($filename)) {
            return $this->cache->get($filename);
        }

        $simulation = $this->loader->load($filename);

        // Add a new entry to the cache
        $this->cache->set($filename, $simulation);

        return $simulation;
    }
}