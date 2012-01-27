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
})->bind('homepage');

$app->match('/change', function() use ($app) {
    $form = $app['form.factory']->createBuilder('form')
                                ->add('file', 'file', array('label' => 'Simulation file'))
                                ->getForm();

    if ('POST' === $app['request']->getMethod()) {
        $form->bindRequest($app['request']);

        if ($form->isValid()) {
            $oldSimulationName = $app['kp.output_file'];
            $newSimulationName = $oldSimulationName.'_new';

            $data = $form->getData();
            $newSimulationFile = $data['file'];

            // Move the file to the correct folder
            $newSimulationFile->move(dirname($newSimulationName), basename($newSimulationName));

            // Is it a correct simulation output file?
            try {
                $app['kp.simulation_manager']->get($newSimulationName, true, false);
                $isCorrect = true;
            } catch (\Exception $e) {
                $isCorrect = false;
            }

            if (false === $isCorrect) {
                // Remove the uncorrect file
                unlink($newSimulationName);
                $app['session']->setFlash('error', 'Invalid format');
            } else {
                // The file is correct
                if (true === file_exists($oldSimulationName)) {
                    unlink($oldSimulationName);
                }

                rename($newSimulationName, $oldSimulationName);
                $app['session']->setFlash('success', 'The simulation output file has been correctly updated.');
            }
        }
    }

    return $app['twig']->render('change.html.twig', array(
        'form' => $form->createView()
    ));
})->bind('change');

$app->get('/js/simulation.js', function() use ($app) {
    $simulation = $app['kp.simulation'];
    $content = $app['twig']->render('simulation.js.twig', array(
        'simulation' => $simulation
    ));

    $response = new Response($content);
    $response->headers->set('Content-Type', 'text/javascript');

    return $response;
});

$app->error(function(\Exception $e, $code) use ($app) {
    return $app['twig']->render('error.html.twig', array(
        'code' => $code,
        'e'    => $e,
    ));
});

return $app;