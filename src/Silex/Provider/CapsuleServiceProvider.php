<?php

namespace JG\Silex\Provider;

use Illuminate\Events\Dispatcher;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Container\Container as CapsuleContainer;
use Pimple\Container as PimpleContainer;
use Pimple\ServiceProviderInterface;
use Silex\Api\BootableProviderInterface;
use Silex\Application;

class CapsuleServiceProvider implements ServiceProviderInterface, BootableProviderInterface
{
    /**
     * Register the Capsule Service.
     *
     * @param PimpleContainer $app
     **/
    public function register(PimpleContainer $app)
    {
        $app['capsule.connectionDefault'] = [
            'driver' => 'mysql',
            'host' => 'localhost',
            'port' => 3306,
            'database' => null,
            'username' => null,
            'password' => null,
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ];

        $app['capsule.connections'] = [];

        $app['capsule.container'] = function (Application $app) {
            return new CapsuleContainer();
        };

        $app['capsule.dispatcher'] = function (Application $app) {
            return new Dispatcher($app['capsule.container']);
        };

        $app['capsule.options'] = [
            'setAsGlobal' => true,
            'bootEloquent' => true,
            'enableQueryLog' => true,
        ];

        $app['capsule'] = function (Application $app) {
            $capsule = new Capsule($app['capsule.container']);

            // Connections
            foreach ($app['capsule.connections'] as $connectionName => $connectionConfig) {
                $connectionConfig += $app['capsule.connectionDefault'];
                $capsule->addConnection($connectionConfig, $connectionName);

                if ($app['capsule.options']['enableQueryLog']) {
                    $capsule->getConnection($connectionName)->enableQueryLog();
                }
            }

            // Set the event dispatcher used by Eloquent models...
            if (!empty($app['capsule.dispatcher'])) {
                $capsule->setEventDispatcher($app['capsule.dispatcher']);
            }

            // Make this Capsule instance available globally via static methods...
            if (!empty($app['capsule.options']['setAsGlobal'])) {
                $capsule->setAsGlobal();
            }

            // Setup the Eloquent ORM... (optional; unless you've used setEventDispatcher())
            if (!empty($app['capsule.options']['bootEloquent'])) {
                $capsule->bootEloquent();
            }

            return $capsule;
        };
    }

    /**
     * Boot the Capsule Service.
     *
     * @param Application $app
     **/
    public function boot(Application $app)
    {
        if ($app['capsule.options']['bootEloquent']) {
            $app->before(function () use ($app) {
                $app['capsule'];
            }, Application::EARLY_EVENT);
        }
    }
}
