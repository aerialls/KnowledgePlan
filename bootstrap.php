<?php

/*
 * This file is part of the KnowledgePlan website.
 *
 * (c) 2012 Julien Brochet <mewt@madalynn.eu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require 'vendor/.composer/autoload.php';

$app = new Silex\Application();

$app['debug'] = true;

// Twig
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/views'
));

// Knowledge Plan
$app->register(new Madalynn\KnowledgePlan\Silex\Provider\KnowledgePlanServiceProvider(), array(
    'kp.force_fresh'  => true,
    'kp.cache_folder' => __DIR__.'/cache',
    'kp.output_file'  => __DIR__.'/output',
    'kp.plot_options' => array()
));