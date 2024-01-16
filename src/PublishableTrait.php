<?php

namespace JobMetric\PackageCore;

use JobMetric\PackageCore\Exceptions\AssetFolderNotFoundException;
use JobMetric\PackageCore\Exceptions\BaseConfigFileNotFoundException;
use JobMetric\PackageCore\Exceptions\ConfigFileNotFoundException;
use JobMetric\PackageCore\Exceptions\MigrationFolderNotFoundException;
use JobMetric\PackageCore\Exceptions\ViewFolderNotFoundException;

trait PublishableTrait
{
    /**
     * publishable dependency
     *
     * @return void
     */
    public function publishableDependency(): void
    {
        if (isset($this->package->option['dependency_publishable'])) {
            $publishables = [];
            foreach ($this->package->option['dependency_publishable'] as $item) {
                $publishables = array_merge($publishables, self::pathsToPublish($item['provider'], $item['group']));
            }

            $this->publishes($publishables, [$this->package->name, $this->package->name . '-dependency']);

            $this->afterPublishableDependencyPackage();
        }
    }

    /**
     * publishable config
     *
     * @return void
     * @throws BaseConfigFileNotFoundException
     *
     * @throws ConfigFileNotFoundException
     */
    public function publishableConfig(): void
    {
        if (isset($this->package->option['hasConfig'])) {
            if ($this->package->option['hasConfig']) {
                $baseConfigFile = realpath($this->package->option['basePath'] . '/../config/config.php');
                if (!file_exists($baseConfigFile)) {
                    $baseConfigFile = realpath($this->package->option['basePath'] . '/../config/' . $this->package->name . '.php');
                }

                if (file_exists($baseConfigFile)) {
                    $this->publishes([
                        $baseConfigFile => config_path($this->package->shortName() . '.php'),
                    ], [$this->package->name, $this->package->name . '-config']);
                } else {
                    throw new BaseConfigFileNotFoundException($this->package->name);
                }

                if (isset($this->package->option['config'])) {
                    foreach ($this->package->option['config'] as $item) {
                        $configFile = realpath($this->package->option['basePath'] . '/../config/' . $item . '.php');

                        if (file_exists($configFile)) {
                            $this->publishes([
                                $configFile => config_path($item . '.php'),
                            ], [$this->package->name, $this->package->name . '-' . $item . '-config']);
                        } else {
                            throw new ConfigFileNotFoundException($this->package->name, $item);
                        }
                    }
                }

                $this->afterPublishableConfigPackage();
            }
        }
    }

    /**
     * publishable migration
     *
     * @return void
     * @throws MigrationFolderNotFoundException
     */
    public function publishableMigration(): void
    {
        if (isset($this->package->option['hasMigration'])) {
            if ($this->package->option['hasMigration']) {
                $migration_path = realpath($this->package->option['basePath'] . '/../database/migrations');

                if ($migration_path) {
                    $this->publishes([
                        $migration_path => database_path('migrations')
                    ], [$this->package->name, $this->package->name . '-migrations']);
                } else {
                    throw new MigrationFolderNotFoundException($this->package->name);
                }

                $this->afterPublishableMigrationPackage();
            }
        }
    }

    /**
     * publishable resources view
     *
     * @return void
     * @throws ViewFolderNotFoundException
     */
    public function publishableView(): void
    {
        if (isset($this->package->option['hasView'])) {
            if ($this->package->option['hasView']) {
                $view_path = realpath($this->package->option['basePath'] . '/../resources/views');

                if ($view_path) {
                    $this->publishes([
                        $view_path => resource_path('views/vendor/' . $this->package->shortName())
                    ], [$this->package->name, $this->package->name . '-views']);
                } else {
                    throw new ViewFolderNotFoundException($this->package->name);
                }

                $this->afterPublishableViewPackage();
            }
        }
    }

    /**
     * publishable asset
     *
     * @return void
     * @throws AssetFolderNotFoundException
     */
    public function publishableAsset(): void
    {
        if (isset($this->package->option['hasAsset'])) {
            if ($this->package->option['hasAsset']) {
                $asset_path = realpath($this->package->option['basePath'] . '/../assets');

                if ($asset_path) {
                    $this->publishes([
                        $asset_path => public_path('assets/vendor/' . $this->package->shortName())
                    ], [$this->package->name, $this->package->name . '-assets']);
                } else {
                    throw new AssetFolderNotFoundException($this->package->name);
                }

                $this->afterPublishableAssetPackage();
            }
        }
    }
}
