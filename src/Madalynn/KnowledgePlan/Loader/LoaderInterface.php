<?php

/*
 * This file is part of the KnowledgePlan website.
 *
 * (c) 2012 Julien Brochet <mewt@madalynn.eu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Madalynn\KnowledgePlan\Loader;

interface LoaderInterface
{
    /**
     * Loads a simulation from a file
     *
     * @param string $filename The filename
     *
     * @return Madalynn\KnwoledgePlan\Simulation
     */
    function load($filename);
}