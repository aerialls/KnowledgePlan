<?php

/*
 * This file is part of the KnowledgePlan website.
 *
 * (c) 2012 Julien Brochet <mewt@madalynn.eu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Madalynn\KnowledgePlan\Utils;

class MathUtils
{
    static public function pgcd($a, $b)
    {
        return ($a % $b) ? self::pgcd($b,$a % $b) : $b;
    }
}