<?php

namespace ServiceLayer\Providers;

use Illuminate\Support\ServiceProvider;
use ServiceLayer\Console\Commands\MakeLayerCommand;

class ServiceLayerServiceProvider extends ServiceProvider
{
    private array $serviceLayers = [];

    public function __construct($app)
    {
        $this->serviceLayers = config('service-layer.layers', []);
        parent::__construct($app);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../../publishables/config/service-layer.php' => config_path('service-layer.php'),
        ]);

        $this->mergeConfigFrom(__DIR__ . '/../../publishables/config/service-layer.php', 'service-layer');

        $this->commands([MakeLayerCommand::class]);

        $this->registerAliases();
    }

    /**
     * Register services.
     */
    public function register(): void
    {
        $this->registerBindings();
    }

    private function registerBindings()
    {
        foreach($this->serviceLayers as $alias => $serviceLayer) {
            foreach($serviceLayer as $type => $components) {
                if(is_array($components)) {
                    [,$class] = $components;
                } else {
                    $class = $components;
                }

                switch ($type) {
                    case 'model':
                        $this->app->bind("{$alias}.{$type}", $class);
                        break;

                    default:
                        $this->app->singleton("{$alias}.{$type}", $class);
                        break;
                }
            }
        }
    }

    private function registerAliases(): void
    {
        foreach($this->serviceLayers as $alias => $serviceLayer) {
            foreach($serviceLayer as $type => $components) {
                if(!is_array($components)) { continue; }
                [$contract,] = $components;
                $this->app->alias("{$alias}.{$type}", $contract);
            }
        }
    }
}
