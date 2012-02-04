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

class ExperienceManager
{
    /**
     * @var array $experiences
     */
    protected $experiences;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->experiences = array();
    }

    /**
     * Adds an experience to the manager
     *
     * @param Experience $experience A instance of Experience
     */
    public function add(Experience $experience)
    {
        $this->experiences[$experience->getName()] = $experience;
    }

    /**
     * Checks if an experience is in the manager
     *
     * @param string $name The name of the experience
     *
     * @return Boolean True if the experience exists
     */
    public function has($name)
    {
        return array_key_exists($name, $this->experiences);
    }

    /**
     * Returns an experience
     *
     * @param string $name The name of the experience
     *
     * @return Experience|null
     */
    public function get($name)
    {
        return $this->has($name) ? $this->experiences[$name] : null;
    }
}