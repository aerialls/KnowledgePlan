<?php

/*
 * This file is part of the KnowledgePlan website.
 *
 * (c) 2012 Julien Brochet <mewt@madalynn.eu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require 'vendor/autoload.php';

use Madalynn\KnowledgePlan\Silex\Provider\KnowledgePlanServiceProvider;

$app = new Silex\Application();

$app['debug'] = true;

$app->register(new Silex\Provider\FormServiceProvider());
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
$app->register(new Silex\Provider\SessionServiceProvider());

// Twig
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path'           => __DIR__.'/views',
    'twig.form.templates' => array('form.html.twig')
));

// Knowledge Plan
$app->register(new KnowledgePlanServiceProvider(), array(
    'kp.cache_folder'        => __DIR__.'/cache',
    'kp.simulations_folder'  => __DIR__.'/simulations',
    'kp.options'             => array(
        'x_label' => 'Outpute rate (packet/ms)',
        'y_label' => 'Delay (ms)'
    ),
    'kp.simulation_options' => array(
        'knowledge_plan' => array(
            'x_max' => 8
        )
    ),
    'kp.experiences' => array(
        'knowledgeplan' => array(
            'title'       => 'Modelling the Knowledge Plan',
            'simulations' => array('knowledge_plan'),
            'plots'       => array('centroids', 'points', 'hlm'), // 'hlm', 'centroids', 'delay_max' or 'points'
            'fields'      => array('time', 'knowledge-plan')
        ),
        'performance' => array(
            'title'               => 'Performance evaluation',
            'simulations'         => array(), // Empty array for all
            'simulations-exclude' => array('knowledge_plan'),
            'plots'               => array('points', 'delay_max'), // 'hlm', 'centroids', 'delay_max' or 'points'
            'fields'              => array(
                'time', 'outputrate-average', 'delay-average',
                'accepted-flows', 'rejected-flows', 'timeslot-with-qos',
                'timeslot-without-qos'
            )
        )
    )
));
