<?php

namespace JobMetric\PackageCore;

use Illuminate\Support\ServiceProvider;
use JobMetric\PackageCore\Exceptions\BaseConfigFileNotFoundException;
use JobMetric\PackageCore\Exceptions\BaseRouteFileNotFoundException;
use JobMetric\PackageCore\Exceptions\ConfigFileNotFoundException;
use JobMetric\PackageCore\Exceptions\InvalidPackageException;
use ReflectionClass;

abstract class PackageCoreServiceProvider extends ServiceProvider
{
    use EventTrait, FactoryTrait;

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

        // register package
        $this->package = new PackageCore;

        $this->package->setBasePath($this->getPackageBaseDir());

        $this->configuration($this->package);

        if (empty($this->package->name)) {
            throw new InvalidPackageException(class_basename($this->package));
        }

        // register classes
        if (isset($this->package->option['classes'])) {
            foreach ($this->package->option['classes'] as $key => $item) {
                if ('bind' == $item['type']) {
                    $this->app->bind($key, $item['class']);
                }
                if ('singleton' == $item['type']) {
                    $this->app->singleton($key, $item['class']);
                }
                if ('scoped' == $item['type']) {
                    $this->app->scoped($key, $item['class']);
                }
            }

            $this->afterRegisterClassPackage();
        }

        // register config file
        if ($this->package->option['hasConfig']) {
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

        $this->afterRegisterPackage();
    }

    /**
     * boot provider
     *
     * @return void
     * @throws BaseRouteFileNotFoundException
     */
    public function boot(): void
    {
        $this->beforeBootPackage();

        // load translation
        if (isset($this->package->option['hasTranslation'])) {
            $this->loadTranslationsFrom($this->package->option['basePath'] . '/../lang', $this->package->shortName());

            $this->translationsLoadedPackage();
        }

        if ($this->app->runningInConsole()) {
            // load migration
            if (isset($this->package->option['hasMigration'])) {
                $this->loadMigrationsFrom($this->package->option['basePath'] . '/../database/migrations');

                $this->migrationLoadedPackage();
            }

            // load command
            if (isset($this->package->option['commands'])) {
                $this->commands($this->package->option['commands']);

                $this->afterRegisterCommandPackage();
            }

            $this->runInConsolePackage();
        } else if ($this->app->runningUnitTests()) {
            $this->runInTestPackage();
        } else {
            $this->runInWebPackage();
        }

        // load route
        if ($this->package->option['hasRoute']) {
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

        $this->afterBootPackage();
    }

    protected function getPackageBaseDir(): string
    {
        $reflector = new ReflectionClass(get_class($this));

        return dirname($reflector->getFileName());
    }
}
