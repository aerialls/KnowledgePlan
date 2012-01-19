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
use Madalynn\KnowledgePlan\Cache\FilesystemCache;
use Madalynn\KnowledgePlan\Loader\CustomLoader;

class KnowledgePlanServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['simulation_manager'] = $app->share(function() use ($app) {
            return new SimulationManager(new FilesystemCache($app['kp.cache_folder']), new CustomLoader());
        });
    }
}