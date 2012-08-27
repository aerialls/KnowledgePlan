<?php

/*
 * This file is part of the KnowledgePlan website.
 *
 * (c) 2012 Julien Brochet <mewt@madalynn.eu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Madalynn\KnowledgePlan\Cache;

interface CacheInterface
{
    /**
     * Checks if the cache has a value for a key.
     *
     * @param string $key A unique key.
     *
     * @return bool Whether the cache has a key.
     */
    public function has($key);

    /**
     * Returns the value for a key.
     *
     * @param string $key A unique key.
     *
     * @return mixed The value for a key.
     */
    public function get($key);

    /**
     * Sets a value for a key.
     *
     * @param string $key   A unique key.
     * @param mixed  $value The value.
     */
    public function set($key, $value);

    /**
     * Removes a value from the cache.
     *
     * @param string $key A unique key.
     */
    public function remove($key);

    /**
     * Clears the cache.
     */
    public function clear();
}
