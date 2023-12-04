<?php

namespace JobMetric\PackageCore;

use Illuminate\Support\ServiceProvider;

class PackageCoreServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('PackageCore', function ($app) {
            return new PackageCore($app);
        });

        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'package-core');
    }

    /**
     * boot provider
     *
     * @return void
     */
    public function boot(): void
    {
        // set translations
        $this->loadTranslationsFrom(realpath(__DIR__.'/../lang'), 'package-core');
    }
}
