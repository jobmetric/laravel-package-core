<?php

namespace JobMetric\PackageCore;

use JobMetric\PackageCore\Exceptions\RegisterClassTypeNotFoundException;
use JobMetric\PackageCore\Enums\RegisterClassTypeEnum;
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
            if(!in_array($config, ['config', $this->name])) {
                $this->option['config'][] = $config;
            }
        }

        return $this;
    }

    /**
     * has migration file in package.
     *
     * @return static
     */
    public function hasMigration(): static
    {
        $this->option['hasMigration'] = true;

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
        if(! in_array($type, RegisterClassTypeEnum::values())) {
            throw new RegisterClassTypeNotFoundException($type);
        }

        if(! isset($this->option['classes'])) {
            $this->option['classes'] = [];
        }

        if(! in_array($key, $this->option['classes'])) {
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
        if(! isset($this->option['commands'])) {
            $this->option['commands'] = [];
        }

        if(! in_array($class, $this->option['commands'])) {
            $this->option['commands'][] = $class;
        }

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
