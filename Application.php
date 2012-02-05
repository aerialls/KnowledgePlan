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

use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Response;
use Madalynn\KnowledgePlan\Symfony\GzipStreamedResponse;

$app->get('/', function() use ($app) {
    return $app['twig']->render('homepage.html.twig');
})->bind('homepage');

$app->get('/experience/{name}', function($name) use ($app) {
    // The controller don't check if the experience exists.
    // Indeed, it will be the aim of the javascript to do it
    return $app['twig']->render('experience.html.twig', array(
        'name' => $name
    ));
})->bind('experience');

$app->match('/options', function() use ($app) {
    $simulations = Finder::create()->files()
                                   ->in($app['kp.simulations_folder'])
                                   ->getIterator();

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
                $app['kp.simulation_manager']->get(pathinfo($newSimulation, PATHINFO_FILENAME), true, false);
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

                // Force creation of the cache file
                $app['kp.simulation_manager']->get($name);

                // Reset form data
                $form->setData(array('name' => ''));
            }
        }
    }

    return $app['twig']->render('options.html.twig', array(
        'simulations' => $simulations,
        'form'        => $form->createView()
    ));
})->bind('options');

$app->get('/js/experience-{name}.js', function($name) use ($app) {
    if (false === $app['kp.experience_manager']->has($name)) {
        return $app->abort(404, sprintf('The experience "%s does not exist.', $name));
    }

    // We use a Gzip for the content encoding to reduce
    // the size of the file. Futhermore, the Streamed response
    // speed up the result
    $response = new GzipStreamedResponse(function() use ($app, $name) {
        echo $app['twig']->render('experience.js.twig', array(
        'experience' => $app['kp.experience_manager']->get($name)
        ));
    });

    $response->headers->set('Content-Type', 'text/javascript');

    return $response;
})->bind('experience_js');

$app->error(function(\Exception $exception, $code) use ($app) {
    return $app['twig']->render('error.html.twig', array(
        'code'      => $code,
        'exception' => $exception,
    ));
});

$app->get('/remove/{filename}', function($filename) use ($app) {
   $path = $app['kp.simulations_folder'].'/'.$filename;

   unlink($path);
   $app['kp.simulation_manager']->remove($filename);

   // Flash
   $app['session']->setFlash('success', sprintf('The simulation "%s" has been correctly removed.', $filename));

   return $app->redirect($app['url_generator']->generate('options'));
})->bind('remove');

return $app;