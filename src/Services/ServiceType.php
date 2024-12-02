<?php

namespace JobMetric\PackageCore\Services;

use Illuminate\Contracts\Foundation\Application;
use JobMetric\PackageCore\Exceptions\ServiceTypeTypeNotFoundException;
use JobMetric\PackageCore\TraitBooter;
use Throwable;

abstract class ServiceType
{
    use TraitBooter;

    /**
     * The type of the service.
     *
     * @var string|null $type
     */
    protected ?string $type = null;

    /**
     * The application instance.
     *
     * @var Application
     */
    protected Application $app;

    /**
     * Create a new Translation instance.
     *
     * @param Application $app
     *
     * @return void
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    abstract protected function serviceType(): string;

    /**
     * Set data in service container
     *
     * @param array $params
     *
     * @return void
     */
    protected function setInContainer(array $params = []): void
    {
        $this->app->singleton($this->serviceType(), function () use ($params) {
            return $params;
        });
    }

    /**
     * Get all data in service container.
     *
     * @return array
     */
    protected function getInContainer(): array
    {
        return app($this->serviceType());
    }

    /**
     * Define the type use in service provider.
     *
     * @param string $type
     *
     * @return static
     */
    public function define(string $type): static
    {
        $this->type = $type;

        $types = $this->getInContainer();
        $types[$type] = [];
        $this->setInContainer($types);

        $this->boot();

        return $this;
    }

    /**
     * set the type use in all system
     *
     * @param string $type
     *
     * @return static
     * @throws Throwable
     */
    public function type(string $type): static
    {
        $types = $this->getInContainer();

        if (isset($types[$type])) {
            $this->type = $type;

            return $this;
        }

        throw new ServiceTypeTypeNotFoundException($this->serviceType(), $type);
    }

    /**
     * Set the type parameter.
     *
     * @param string $key
     * @param mixed $params
     *
     * @return void
     */
    protected function setTypeParam(string $key, mixed $params): void
    {
        $types = $this->getInContainer();

        $types[$this->type][$key] = $params;

        $this->setInContainer($types);
    }

    /**
     * Get the type data.
     *
     * @return array
     */
    public function get(): array
    {
        $types = $this->getInContainer();

        return $types[$this->type];
    }

    /**
     * Get the key in data type param.
     *
     * @param string $key
     * @param mixed $default
     *
     * @return mixed
     */
    protected function getTypeParam(string $key, mixed $default = null): mixed
    {
        $types = $this->get();

        if (isset($types[$key])) {
            return $types[$key];
        }

        return $default;
    }
}
