<?php

/*
 * This file is part of the KnowledgePlan website.
 *
 * (c) 2012 Julien Brochet <mewt@madalynn.eu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Madalynn\KnowledgePlan\Silex\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;

use Madalynn\KnowledgePlan\Experience\ExperienceBuilder;
use Madalynn\KnowledgePlan\Experience\ExperienceManager;
use Madalynn\KnowledgePlan\Simulation\SimulationManager;
use Madalynn\KnowledgePlan\Simulation\SimulationBuilder;
use Madalynn\KnowledgePlan\Cache\FilesystemCache;
use Madalynn\KnowledgePlan\Cache\EmptyCache;

use Symfony\Component\Finder\Finder;

class KnowledgePlanServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        if (!is_dir($app['kp.cache_folder'])) {
            // Creation of the cache folder
            mkdir($app['kp.cache_folder']);
        }

        if (!is_dir($app['kp.simulations_folder'])) {
            // Creation of the simulations folder
            mkdir($app['kp.simulations_folder']);
        }

        $app['kp.simulation_manager'] = $app->share(function() use ($app) {
            // If the application is in dry run mode
            // Then the cache can't be used to store
            // all the simulations
            $cache = (isset($app['dry_run']) && $app['dry_run']) ? new EmptyCache() : new FilesystemCache($app['kp.cache_folder']);

            return new SimulationManager($app['kp.simulation_builder'], $cache, $app['kp.simulations_folder']);
        });

        $app['kp.simulation_builder'] = $app->share(function() use ($app) {
            $options      = isset($app['kp.options']) ? $app['kp.options'] : array();
            $localOptions = isset($app['kp.simulation_options']) ? $app['kp.simulation_options'] : array();

            return new SimulationBuilder($options, $localOptions);
        });

        $app['kp.experience_manager'] = $app->share(function() use ($app) {
            $manager     = new ExperienceManager();
            $experiences = $app['kp.experiences'];

            foreach ($experiences as $name => $options) {
                // Check for no simulations
                if (empty($options['simulations'])) {
                    $files = Finder::create()->files()
                                             ->in($app['kp.simulations_folder'])
                                             ->getIterator();

                    $simulations = array();
                    foreach ($files as $file) {
                        $simulations[] = $file->getFilename();
                    }

                    $options['simulations'] = $simulations;
                }

                // Remove exclude simulations
                if (isset($options['simulations-exclude'])) {
                    foreach ($options['simulations-exclude'] as $exclude) {
                        if (false !== $pos = array_search($exclude, $options['simulations'])) {
                            // Remove the simulation from the array
                            unset($options['simulations'][$pos]);
                        }
                    }

                    // Reindex the array
                    $options['simulations'] = array_values($options['simulations']);
                }

                $experience = $app['kp.experience_builder']->create($name, $options);
                $manager->add($experience);
            }

            return $manager;
        });

        $app['kp.experience_builder'] = $app->share(function() use ($app) {
            return new ExperienceBuilder($app['kp.simulation_manager']);
        });
    }
}