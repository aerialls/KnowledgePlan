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

$app->register(new Silex\Provider\SymfonyBridgesServiceProvider());
$app->register(new Silex\Provider\FormServiceProvider());
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
$app->register(new Silex\Provider\SessionServiceProvider());

// Twig
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path'           => __DIR__.'/views',
    'twig.form.templates' => array('form.html.twig')
));

// Knowledge Plan
$app->register(new Madalynn\KnowledgePlan\Silex\Provider\KnowledgePlanServiceProvider(), array(
    'kp.cache_folder'        => __DIR__.'/cache',
    'kp.simulations_folder'  => __DIR__.'/simulations',
    'kp.options'             => array(
        'x_label' => 'Outpute rate (packet/ms)',
        'y_label' => 'Delay (ms)'
    ),
    'kp.simulation_options' => array(),
    'kp.experiences' => array(
        'knowledgeplan' => array(
            'title'       => 'Modelling the Knowledge Plan',
            'simulations' => array('knowledge_plan'),
            'plots'       => array('centroids', 'points', 'hlm') // 'hlm', 'centroids', 'delay_max' or 'points'
        ),
        'performance' => array(
            'title'       => 'Performance evaluation',
            'simulations' => array('knowledge_plan', 'default'),
            'plots'       => array('points', 'delay_max') // 'hlm', 'centroids', 'delay_max' or 'points'
        )
    )
));