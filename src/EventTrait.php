<?php

namespace JobMetric\PackageCore;

trait EventTrait
{
    /**
     * before register package
     *
     * @return void
     */
    public function beforeRegisterPackage(): void
    {
    }

    /**
     * after register package
     *
     * @return void
     */
    public function afterRegisterPackage(): void
    {
    }

    /**
     * before new instance package
     *
     * @return void
     */
    public function beforeNewInstancePackage(): void
    {
    }

    /**
     * after new instance package
     *
     * @return void
     */
    public function afterNewInstancePackage(): void
    {
    }

    /**
     * before boot package
     *
     * @return void
     */
    public function beforeBootPackage(): void
    {
    }

    /**
     * after boot package
     *
     * @return void
     */
    public function afterBootPackage(): void
    {
    }

    /**
     * run in console package
     *
     * @return void
     */
    public function runInConsolePackage(): void
    {
    }

    /**
     * run in test package
     *
     * @return void
     */
    public function runInTestPackage(): void
    {
    }

    /**
     * run in web package
     *
     * @return void
     */
    public function runInWebPackage(): void
    {
    }

    /**
     * config loaded package
     *
     * @return void
     */
    public function configLoadedPackage(): void
    {
    }

    /**
     * migration loaded package
     *
     * @return void
     */
    public function migrationLoadedPackage(): void
    {
    }

    /**
     * resource view loaded package
     *
     * @return void
     */
    public function viewLoadedPackage(): void
    {
    }

    /**
     * translations loaded package
     *
     * @return void
     */
    public function translationsLoadedPackage(): void
    {
    }

    /**
     * after register class package
     *
     * @return void
     */
    public function afterRegisterClassPackage(): void
    {
    }

    /**
     * after register command package
     *
     * @return void
     */
    public function afterRegisterCommandPackage(): void
    {
    }

    /**
     * after register command package
     *
     * @return void
     */
    public function afterRegisterPublishablePackage(): void
    {
    }

    /**
     * after publishable dependency package
     *
     * @return void
     */
    public function afterPublishableDependencyPackage(): void
    {
    }

    /**
     * after publishable config package
     *
     * @return void
     */
    public function afterPublishableConfigPackage(): void
    {
    }

    /**
     * after publishable migration package
     *
     * @return void
     */
    public function afterPublishableMigrationPackage(): void
    {
    }

    /**
     * after publishable resources view package
     *
     * @return void
     */
    public function afterPublishableViewPackage(): void
    {
    }

    /**
     * after publishable asset package
     *
     * @return void
     */
    public function afterPublishableAssetPackage(): void
    {
    }
}
