<?php

namespace JobMetric\PackageCore\Exceptions;

use Exception;
use Throwable;

class InvalidPackageException extends Exception
{
    public function __construct(string $package, int $code = 400, ?Throwable $previous = null)
    {
        parent::__construct("The package $package is invalid.", $code, $previous);
    }
}
