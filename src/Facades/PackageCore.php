<?php

namespace JobMetric\PackageCore\Facades;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Facade;

/**
 * @see \JobMetric\PackageCore\PackageCore
 */
class PackageCore extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'PackageCore';
    }
}
