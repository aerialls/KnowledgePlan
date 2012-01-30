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
                                ->add('name', 'text', array('label' => 'Name'))
                                ->add('file', 'file', array('label' => 'Simulation file'))
                                ->getForm();

    if ('POST' === $app['request']->getMethod()) {
        $form->bindRequest($app['request']);

        if ($form->isValid()) {
            $data = $form->getData();

            $file = $data['file'];
            $name = $data['name'];

            $oldSimulationName = $app['kp.simulations_folder'].'/'.$name;
            $newSimulationName = $oldSimulationName.'_new';

            // Check the file mimetype
            if ('text/plain' !== $file->getClientMimeType()) {
                throw new \InvalidArgumentException('The file mimetype must be "text/plain".');
            }

            // Move the file to the correct folder
            $file->move(dirname($newSimulationName), basename($newSimulationName));

            // Is it a correct simulation output file?
            try {
                $app['kp.simulation_manager']->get($newSimulationName, true, false);
                $correct = true;
            } catch (\Exception $e) {
                $correct = false;
            }

            if (false === $correct) {
                // Remove the uncorrect file
                unlink($newSimulationName);
                $app['session']->setFlash('error', 'Invalid format');
            } else {
                // The file is correct
                if (true === file_exists($oldSimulationName)) {
                    unlink($oldSimulationName);
                }

                $app['kp.simulation_manager']->remove($oldSimulationName);
                rename($newSimulationName, $oldSimulationName);
                $app['session']->setFlash('success', sprintf('The simulation output "%s" has been correctly updated.', $name));
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