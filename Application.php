<?php

/*
 * This file is part of the KnowledgePlan website.
 *
 * (c) 2012 Julien Brochet <mewt@madalynn.eu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once 'bootstrap.php';

use Symfony\Component\HttpFoundation\Response;

$app->get('/', function() use ($app) {

    return $app['twig']->render('homepage.html.twig');
});

$app->get('/js/simulation.js', function() use ($app) {
    $simulation = $app['kp.simulation'];
    $content = $app['twig']->render('simulation.js.twig', array(
        'simulation' => $simulation
    ));

    $response = new Response($content);
    $response->headers->set('Content-Type', 'text/javascript');

    return $response;
});

return $app;