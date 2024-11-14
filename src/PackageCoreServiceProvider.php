<?php

namespace JobMetric\PackageCore;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use JobMetric\PackageCore\Exceptions\AssetFolderNotFoundException;
use JobMetric\PackageCore\Exceptions\BaseConfigFileNotFoundException;
use JobMetric\PackageCore\Exceptions\BaseRouteFileNotFoundException;
use JobMetric\PackageCore\Exceptions\ConfigFileNotFoundException;
use JobMetric\PackageCore\Exceptions\InvalidPackageException;
use JobMetric\PackageCore\Exceptions\MigrationFolderNotFoundException;
use JobMetric\PackageCore\Exceptions\ViewFolderNotFoundException;
use JobMetric\PackageCore\View\Components\BooleanStatus;

abstract class PackageCoreServiceProvider extends ServiceProvider
{
    use EventTrait, FactoryTrait, ProviderTrait;

    /**
     * The package core object.
     *
     * @var PackageCore
     */
    protected PackageCore $package;

    /**
     * set configuration package
     *
     * @param PackageCore $package
     *
     * @return void
     */
    abstract public function configuration(PackageCore $package): void;

    /**
     * register provider
     *
     * @return void
     * @throws BaseConfigFileNotFoundException
     * @throws ConfigFileNotFoundException
     * @throws InvalidPackageException
     */
    public function register(): void
    {
        // load configuration for package core
        global $package_core_config_register;
        if (!$package_core_config_register) {
            $package_core_config_register = true;

            // load view
            $this->loadViewsFrom(realpath(__DIR__ . '/../resources/views'), 'package-core');
        }

        $this->beforeRegisterPackage();

        // factory resolver
        $this->factoryResolver();

        // registration package
        $this->registerPackage();
        $this->registerConfig();
        $this->registerClass();
        $this->loadView();
        $this->loadConsoleKernel();

        $this->afterRegisterPackage();
    }

    /**
     * boot provider
     *
     * @return void
     * @throws BaseConfigFileNotFoundException
     * @throws BaseRouteFileNotFoundException
     * @throws ConfigFileNotFoundException
     * @throws AssetFolderNotFoundException
     * @throws MigrationFolderNotFoundException
     * @throws ViewFolderNotFoundException
     */
    public function boot(): void
    {
        // load configuration for package core
        global $package_core_config_boot;
        if (!$package_core_config_boot) {
            $package_core_config_boot = true;

            // register assets
            $this->publishes([
                realpath(__DIR__ . '/../assets') => public_path('assets/vendor/package-core')
            ], ['package-core', 'package-core-assets']);

            // load translation
            $this->loadTranslationsFrom(realpath(__DIR__ . '/../lang'), 'package-core');

            // add alias for components
            Blade::component(BooleanStatus::class, 'boolean-status');
        }

        $this->beforeBootPackage();

        // bootable package
        $this->loadTranslation();
        $this->loadRoute();

        if ($this->app->runningInConsole()) {
            // bootable package in console
            $this->loadMigration();
            $this->registerCommand();
            $this->registerPublishable();

            $this->runInConsolePackage();
        } else if ($this->app->runningUnitTests()) {
            // bootable package in test

            $this->runInTestPackage();
        } else {
            // bootable package in web
            $this->loadComponent();

            $this->runInWebPackage();
        }

        $this->afterBootPackage();
    }
}
