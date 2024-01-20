<?php

namespace JobMetric\PackageCore;

use JobMetric\PackageCore\Enums\RegisterClassTypeEnum;
use JobMetric\PackageCore\Exceptions\AssetFolderNotFoundException;
use JobMetric\PackageCore\Exceptions\BaseConfigFileNotFoundException;
use JobMetric\PackageCore\Exceptions\BaseRouteFileNotFoundException;
use JobMetric\PackageCore\Exceptions\ConfigFileNotFoundException;
use JobMetric\PackageCore\Exceptions\InvalidPackageException;
use JobMetric\PackageCore\Exceptions\MigrationFolderNotFoundException;
use JobMetric\PackageCore\Exceptions\ViewFolderNotFoundException;
use ReflectionClass;

trait ProviderTrait
{
    use PublishableTrait;

    /**
     * register package
     *
     * @return void
     * @throws InvalidPackageException
     */
    public function registerPackage(): void
    {
        $this->beforeNewInstancePackage();

        $this->package = new PackageCore;

        $this->package->setBasePath($this->getPackageBaseDir());

        $this->configuration($this->package);

        if (empty($this->package->name)) {
            throw new InvalidPackageException(class_basename($this->package));
        }

        $this->afterNewInstancePackage();
    }

    private function getPackageBaseDir(): string
    {
        $reflector = new ReflectionClass(get_class($this));

        return dirname($reflector->getFileName());
    }

    /**
     * register class
     *
     * @return void
     */
    public function registerClass(): void
    {
        if (isset($this->package->option['classes'])) {
            foreach ($this->package->option['classes'] as $key => $item) {
                if (RegisterClassTypeEnum::BIND() == $item['type']) {
                    $this->app->bind($key, $item['class']);
                }
                if (RegisterClassTypeEnum::SINGLETON() == $item['type']) {
                    $this->app->singleton($key, $item['class']);
                }
                if (RegisterClassTypeEnum::SCOPED() == $item['type']) {
                    $this->app->scoped($key, $item['class']);
                }
                if (RegisterClassTypeEnum::REGISTER() == $item['type']) {
                    $this->app->register($item['class']);
                }
            }

            $this->afterRegisterClassPackage();
        }
    }

    /**
     * register config
     *
     * @return void
     * @throws ConfigFileNotFoundException
     * @throws BaseConfigFileNotFoundException
     */
    public function registerConfig(): void
    {
        if (isset($this->package->option['hasConfig'])) {
            $baseConfigFile = realpath($this->package->option['basePath'] . '/../config/config.php');
            if (!file_exists($baseConfigFile)) {
                $baseConfigFile = realpath($this->package->option['basePath'] . '/../config/' . $this->package->name . '.php');
            }

            if (file_exists($baseConfigFile)) {
                $this->mergeConfigFrom($baseConfigFile, $this->package->shortName());
            } else {
                throw new BaseConfigFileNotFoundException($this->package->name);
            }

            if (isset($this->package->option['config'])) {
                foreach ($this->package->option['config'] as $item) {
                    $configFile = realpath($this->package->option['basePath'] . '/../config/' . $item . '.php');

                    if (file_exists($configFile)) {
                        $this->mergeConfigFrom($configFile, $item);
                    } else {
                        throw new ConfigFileNotFoundException($this->package->name, $item);
                    }
                }
            }

            $this->configLoadedPackage();
        }
    }

    /**
     * load translation
     *
     * @return void
     */
    public function loadTranslation(): void
    {
        if (isset($this->package->option['hasTranslation'])) {
            $this->loadTranslationsFrom($this->package->option['basePath'] . '/../lang', $this->package->shortName());

            $this->translationsLoadedPackage();
        }
    }

    /**
     * load migration
     *
     * @return void
     */
    public function loadMigration(): void
    {
        if (isset($this->package->option['hasMigration'])) {
            $this->loadMigrationsFrom($this->package->option['basePath'] . '/../database/migrations');

            $this->migrationLoadedPackage();
        }
    }

    /**
     * load resources view
     *
     * @return void
     */
    public function loadView(): void
    {
        if (isset($this->package->option['hasView'])) {
            $this->loadViewsFrom($this->package->option['basePath'] . '/../resources/views', $this->package->shortName());

            $this->viewLoadedPackage();
        }
    }

    /**
     * register command
     *
     * @return void
     */
    public function registerCommand(): void
    {
        if (isset($this->package->option['commands'])) {
            $this->commands($this->package->option['commands']);

            $this->afterRegisterCommandPackage();
        }
    }

    /**
     * register publishable
     *
     * @return void
     * @throws BaseConfigFileNotFoundException
     * @throws ConfigFileNotFoundException
     * @throws AssetFolderNotFoundException
     * @throws MigrationFolderNotFoundException
     * @throws ViewFolderNotFoundException
     */
    public function registerPublishable(): void
    {
        $this->publishableDependency();
        $this->publishableConfig();
        $this->publishableMigration();

        // publishable view
        if (isset($this->package->option['isPublishableView'])) {
            if ($this->package->option['isPublishableView']) {
                $this->publishableView();
            }
        }

        // publishable asset
        if (isset($this->package->option['hasAsset'])) {
            if ($this->package->option['hasAsset']) {
                $this->publishableAsset();
            }
        }

        if (isset($this->package->option['publishable'])) {
            foreach ($this->package->option['publishable'] as $item) {
                $this->publishes($item['paths'], $item['groups']);
            }

            $this->afterRegisterPublishablePackage();
        }
    }

    /**
     * load route
     *
     * @return void
     * @throws BaseRouteFileNotFoundException
     */
    public function loadRoute(): void
    {
        if (isset($this->package->option['hasRoute'])) {
            $routeFile = realpath($this->package->option['basePath'] . '/../routes/route.php');
            if (!file_exists($routeFile)) {
                $routeFile = realpath($this->package->option['basePath'] . '/../routes/' . $this->package->name . '.php');
            }

            if (file_exists($routeFile)) {
                $this->loadRoutesFrom($routeFile);
            } else {
                throw new BaseRouteFileNotFoundException($this->package->name, 'route');
            }
        }
    }
}
