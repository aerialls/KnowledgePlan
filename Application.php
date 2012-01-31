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
use Madalynn\KnowledgePlan\Symfony\GzipStreamedResponse;

$app->get('/', function() use ($app) {
    return $app['twig']->render('homepage.html.twig');
})->bind('homepage');

$app->match('/options', function() use ($app) {
    $form = $app['form.factory']->createBuilder('form')
                                ->add('name', 'text', array('label' => 'Name'))
                                ->add('file', 'file', array('label' => 'Simulation file'))
                                ->getForm();

    if ('POST' === $app['request']->getMethod()) {
        $form->bindRequest($app['request']);

        if ($form->isValid()) {
            $data = $form->getData();

            $file = $data['file'];
            $name = strtolower($data['name']);

            $simulation = $app['kp.simulations_folder'].'/'.$name;
            $newSimulation = $simulation.'_new';

            // Check the file mimetype
            if ('text/plain' !== $file->getClientMimeType()) {
                throw new \InvalidArgumentException('The file mimetype must be "text/plain".');
            }

            // Move the file to the correct folder
            $file->move(dirname($newSimulation), basename($newSimulation));

            // Is it a correct simulation output file?
            try {
                $app['kp.simulation_manager']->get($newSimulation, true, false);
                $correct = true;
            } catch (\Exception $e) {
                $correct = false;
            }

            if (false === $correct) {
                // Remove the uncorrect file
                unlink($newSimulation);
                $app['session']->setFlash('error', 'Invalid format');
            } else {
                // The file is correct
                if (true === file_exists($simulation)) {
                    $app['kp.simulation_manager']->remove($simulation);
                    unlink($simulation);
                }

                rename($newSimulation, $simulation);
                $app['session']->setFlash('success', sprintf('The simulation output "%s" has been correctly updated.', $name));
            }
        }
    }

    return $app['twig']->render('options.html.twig', array(
        'form' => $form->createView()
    ));
})->bind('options');

$app->get('/js/experience.js', function() use ($app) {
    // We use a Gzip for the content encoding to reduce
    // the size of the file. Futhermore, the Streamed response
    // speed up the result
    $response = new GzipStreamedResponse(function() use ($app) {
        echo $app['twig']->render('experience.js.twig', array(
        'experience' => $app['kp.experience']
        ));
    });

    $response->headers->set('Content-Type', 'text/javascript');

    return $response;
});

$app->error(function(\Exception $exception, $code) use ($app) {
    return $app['twig']->render('error.html.twig', array(
        'code'      => $code,
        'exception' => $exception,
    ));
});

return $app;