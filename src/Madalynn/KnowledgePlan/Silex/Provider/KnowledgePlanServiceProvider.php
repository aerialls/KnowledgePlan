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

use Madalynn\KnowledgePlan\Experience;
use Madalynn\KnowledgePlan\Simulation\SimulationManager;
use Madalynn\KnowledgePlan\Simulation\SimulationBuilder;
use Madalynn\KnowledgePlan\Cache\FilesystemCache;
use Madalynn\KnowledgePlan\Cache\EmptyCache;

class KnowledgePlanServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {

        $app['kp.simulation_manager'] = $app->share(function() use ($app) {
            // If the application is in dry run mode
            // Then the cache can't be used to store
            // all the simulations
            $cache = (isset($app['dry_run']) && $app['dry_run']) ? new EmptyCache() : new FilesystemCache($app['kp.cache_folder']);

            return new SimulationManager($app['kp.simulation_builder'], $cache);
        });

        $app['kp.simulation_builder'] = $app->share(function() use ($app) {
            $options = isset($app['kp.plot_options']) ? $app['kp.plot_options'] : array();

            return new SimulationBuilder($options);
        });

        $app['kp.experience'] = $app->share(function() use ($app) {
            // Add the folder prefix to all the simulations
            $simulations = array_map(function($element) use ($app) {
                return $app['kp.simulations_folder'].'/'.$element;
            }, $app['kp.simulations']);

            return new Experience($simulations, $app['kp.simulation_manager']);
        });
    }
}