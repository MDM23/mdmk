<?php

namespace MDM23\Projdoc\Laravel;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . "/../../resources/config.php",
            "projdoc"
        );
    }

    public function boot()
    {
        if (!$this->app->routesAreCached()) {
            require __DIR__ . "/../../resources/routes.php";
        }

        $this->publishes([
            __DIR__ . "/../../resources/config.php" => config_path("projdoc.php")
        ]);

        $this->loadViewsFrom(
            __DIR__ . "/../../resources/views",
            "projdoc"
        );
    }
}
