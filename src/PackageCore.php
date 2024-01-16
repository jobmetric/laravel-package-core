<?php

namespace JobMetric\PackageCore;

use JobMetric\PackageCore\Enums\RegisterClassTypeEnum;
use JobMetric\PackageCore\Enums\RegisterPublishableTypeEnum;
use JobMetric\PackageCore\Exceptions\AssetFolderNotFoundException;
use JobMetric\PackageCore\Exceptions\DependencyPublishableClassNotFoundException;
use JobMetric\PackageCore\Exceptions\MigrationFolderNotFoundException;
use JobMetric\PackageCore\Exceptions\RegisterClassTypeNotFoundException;
use JobMetric\PackageCore\Exceptions\RegisterPublishableTypeNotFoundException;
use JobMetric\PackageCore\Exceptions\ViewFolderNotFoundException;
use Str;

class PackageCore
{
    /**
     * The package name.
     *
     * @var string
     */
    public string $name;

    /**
     * The package option.
     *
     * @var array
     */
    public array $option = [];

    /**
     * set package name.
     *
     * @param string $name
     *
     * @return static
     */
    public function name(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * has config file in package.
     *
     * @param array $configs Config files other than the main config
     *
     * @return static
     */
    public function hasConfig(array $configs = []): static
    {
        $this->option['hasConfig'] = true;

        foreach ($configs as $config) {
            if (!in_array($config, ['config', $this->name])) {
                $this->option['config'][] = $config;
            }
        }

        return $this;
    }

    /**
     * has migration file in package.
     *
     * @return static
     * @throws MigrationFolderNotFoundException
     */
    public function hasMigration(): static
    {
        $migration_path = realpath($this->option['basePath'] . '/../database/migrations');

        if($migration_path) {
            $this->option['hasMigration'] = true;
        } else {
            throw new MigrationFolderNotFoundException($this->name);
        }

        return $this;
    }

    /**
     * has view file in package.
     *
     * @param bool $publishable
     *
     * @return static
     * @throws ViewFolderNotFoundException
     */
    public function hasView(bool $publishable = false): static
    {
        $view_path = realpath($this->option['basePath'] . '/../resources/views');

        if($view_path) {
            $this->option['hasView'] = true;
            $this->option['isPublishableView'] = $publishable;
        } else {
            throw new ViewFolderNotFoundException($this->name);
        }

        return $this;
    }

    /**
     * has route file in package.
     *
     * @return static
     */
    public function hasRoute(): static
    {
        $this->option['hasRoute'] = true;

        return $this;
    }

    /**
     * has translation file in package.
     *
     * @return static
     */
    public function hasTranslation(): static
    {
        $this->option['hasTranslation'] = true;

        return $this;
    }

    /**
     * has asset files in package.
     *
     * @param bool $publishable
     *
     * @return static
     * @throws AssetFolderNotFoundException
     */
    public function hasAsset(bool $publishable = false): static
    {
        $view_path = realpath($this->option['basePath'] . '/../assets');

        if($view_path) {
            $this->option['hasAsset'] = true;
        } else {
            throw new AssetFolderNotFoundException($this->name);
        }

        return $this;
    }

    /**
     * register class in package.
     *
     * @param string $key
     * @param string $class
     * @param string $type
     *
     * @return static
     * @throws RegisterClassTypeNotFoundException
     */
    public function registerClass(string $key, string $class, string $type = 'bind'): static
    {
        if (!in_array($type, RegisterClassTypeEnum::values())) {
            throw new RegisterClassTypeNotFoundException($type);
        }

        if (!isset($this->option['classes'])) {
            $this->option['classes'] = [];
        }

        if (!in_array($key, $this->option['classes'])) {
            $this->option['classes'][$key] = [
                'class' => $class,
                'type' => $type,
            ];
        }

        return $this;
    }

    /**
     * register command in package.
     *
     * @param string $class
     *
     * @return static
     */
    public function registerCommand(string $class): static
    {
        if (!isset($this->option['commands'])) {
            $this->option['commands'] = [];
        }

        if (!in_array($class, $this->option['commands'])) {
            $this->option['commands'][] = $class;
        }

        return $this;
    }

    /**
     * register publishable in package.
     *
     * @param array $paths
     * @param string|array|null $groups
     *
     * @return static
     */
    public function registerPublishable(array $paths, string|array|null $groups = null): static
    {
        if (is_null($groups)) {
            $groups = $this->name;
        }

        if (is_string($groups)) {
            $groups = [$groups];
        }

        if (!in_array($this->name, $groups)) {
            $groups[] = $this->name;
        }

        if (!isset($this->option['publishable'])) {
            $this->option['publishable'] = [];
        }

        $group = md5(implode(',', $groups));

        $this->option['publishable'][$group] = [
            'paths' => $paths,
            'groups' => $groups,
        ];

        return $this;
    }

    /**
     * register dependency publishable in package.
     *
     * @param string $provider
     * @param string|null $group
     *
     * @return static
     * @throws DependencyPublishableClassNotFoundException
     */
    public function registerDependencyPublishable(string $provider, string $group = null): static
    {
        if(!class_exists($provider)) {
            throw new DependencyPublishableClassNotFoundException($this->name, $provider);
        }

        if (!isset($this->option['dependency_publishable'])) {
            $this->option['dependency_publishable'] = [];
        }

        $this->option['dependency_publishable'][] = [
            'provider' => $provider,
            'group' => $group,
        ];

        return $this;
    }

    public function shortName(): string
    {
        return Str::after($this->name, 'laravel-');
    }

    public function setBasePath(string $path): static
    {
        $this->option['basePath'] = $path;

        return $this;
    }
}
