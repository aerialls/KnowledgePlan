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

class Simulation
{
    protected $informations = array();

    protected $plots = array();

    public function addInformations($time, array $informations)
    {
        $this->informations[$time] = $informations;
    }

    public function addPlot($time, array $points)
    {
        $this->plots[$time] = $points;
    }

    public function getInformations()
    {
        return $this->informations;
    }

    public static function __set_state($data)
    {
        $simulation = new Simulation();

        $simulation->informations = $data['informations'];
        $simulation->plots = $data['plots'];

        return $simulation;
    }
}