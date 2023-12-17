<?php

namespace JobMetric\PackageCore\Exceptions;

use Exception;
use Throwable;

class DependencyPublishableClassNotFoundException extends Exception
{
    public function __construct(string $package, string $class, int $code = 400, ?Throwable $previous = null)
    {
        parent::__construct("Dependency publishable class {$class} not found in package {$package}", $code, $previous);
    }
}
