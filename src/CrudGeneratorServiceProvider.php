<?php

namespace Aslnbxrz\CrudGenerator;

use Aslnbxrz\CrudGenerator\Commands\CrudGeneratorCommand;
use Illuminate\Support\ServiceProvider;

class CrudGeneratorServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->offerPublishing();

        if ($this->app->runningInConsole()) {
            $this->commands([
                CrudGeneratorCommand::class
            ]);
        }
    }

    protected function offerPublishing()
    {

        $this->publishes([
            __DIR__.'/Stubs/Controller.stub' => resource_path('Stubs/Controller.stub'),
            __DIR__.'/Stubs/Model.stub' => resource_path('Stubs/Model.stub'),
            __DIR__.'/Stubs/Route.stub' => resource_path('Stubs/Route.stub'),
        ], 'stubs');

    }
}
