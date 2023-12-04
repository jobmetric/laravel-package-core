<?php

namespace JobMetric\PackageCore;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class PackageCore
{
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
     * @throws BindingResolutionException
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }
}
