<?php

namespace JobMetric\PackageCore\Exceptions;

use Exception;
use Throwable;

class ConfigFileNotFoundException extends Exception
{
    public function __construct(string $package, string $config, int $code = 400, ?Throwable $previous = null)
    {
        parent::__construct(sprintf('The config file %s not found in package %s.', $config, $package), $code, $previous);
    }
}
