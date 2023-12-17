<?php

namespace JobMetric\PackageCore\Exceptions;

use Exception;
use Throwable;

class ConfigFileNotFoundException extends Exception
{
    public function __construct(string $package, string $config, int $code = 400, ?Throwable $previous = null)
    {
        parent::__construct("The config file $config not found in package $package.", $code, $previous);
    }
}
