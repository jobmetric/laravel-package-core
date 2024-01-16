<?php

namespace JobMetric\PackageCore;

use Illuminate\Support\ServiceProvider;
use JobMetric\PackageCore\Exceptions\AssetFolderNotFoundException;
use JobMetric\PackageCore\Exceptions\BaseConfigFileNotFoundException;
use JobMetric\PackageCore\Exceptions\BaseRouteFileNotFoundException;
use JobMetric\PackageCore\Exceptions\ConfigFileNotFoundException;
use JobMetric\PackageCore\Exceptions\InvalidPackageException;
use JobMetric\PackageCore\Exceptions\MigrationFolderNotFoundException;
use JobMetric\PackageCore\Exceptions\ViewFolderNotFoundException;

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
        $this->beforeRegisterPackage();

        // factory resolver
        $this->factoryResolver();

        // registration package
        $this->registerPackage();
        $this->registerConfig();
        $this->registerClass();
        $this->loadView();

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

            $this->runInWebPackage();
        }

        $this->afterBootPackage();
    }
}
