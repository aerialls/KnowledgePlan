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

    public function __construct()
    {
        $this->experiences = array();
    }

    public function add(Experience $experience)
    {
        $this->experiences[$experience->getName()] = $experience;
    }

    public function has($name)
    {
        return array_key_exists($name, $this->experiences);
    }

    public function get($name)
    {
        return $this->has($name) ? $this->experiences[$name] : null;
    }
}