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

use Madalynn\KnowledgePlan\SimulationManager;
use Madalynn\KnowledgePlan\SimulationBuilder;
use Madalynn\KnowledgePlan\Cache\FilesystemCache;

class KnowledgePlanServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['kp.simulation_manager'] = $app->share(function() use ($app) {
            return new SimulationManager($app['kp.simulation_builder'], new FilesystemCache($app['kp.cache_folder']));
        });

        $app['kp.simulation_builder'] = $app->share(function() {
            return new SimulationBuilder();
        });

        $app['kp.simulation'] = $app->share(function() use ($app) {
            return $app['kp.simulation_manager']->get($app['kp.output_file']);
        });
    }
}