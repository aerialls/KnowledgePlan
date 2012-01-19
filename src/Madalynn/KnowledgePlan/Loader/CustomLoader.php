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

use Madalynn\KnowledgePlan\Simulation;

class CustomLoader implements LoaderInterface
{
    /**
     * {@inheritdoc}
     */
    public function load($filename)
    {
        if (false === file_exists($filename)) {
            throw new \InvalidArgumentException(sprintf('The file "%s" does not exist.', $filename));
        }

        return new Simulation();
    }
}