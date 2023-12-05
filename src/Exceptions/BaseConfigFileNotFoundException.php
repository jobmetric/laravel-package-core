<?php

namespace JobMetric\PackageCore\Exceptions;

use Exception;
use Throwable;

class BaseConfigFileNotFoundException extends Exception
{
    public function __construct(string $package, int $code = 400, ?Throwable $previous = null)
    {
        parent::__construct(sprintf('The base config file %s not found.', $package), $code, $previous);
    }
}
