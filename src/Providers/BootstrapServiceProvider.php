<?php

namespace Qortex\Bootstrap\Providers;

use Illuminate\Support\ServiceProvider;

class BootstrapServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadViewsFrom(__DIR__ . '/../../resources/', 'qortex');

        if ($this->app->runningInConsole()) {
            $this->commands([
                \Qortex\Bootstrap\Commands\ServiceMakeCommand::class,
                \Qortex\Bootstrap\Commands\ModelMakeCommand::class,
                \Qortex\Bootstrap\Commands\DeployBranchCommand::class,
            ]);
        }
    }
}
