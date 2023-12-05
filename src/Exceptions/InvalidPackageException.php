<?php

namespace JobMetric\PackageCore\Exceptions;

use Exception;
use Throwable;

class InvalidPackageException extends Exception
{
    public function __construct(string $package, int $code = 400, ?Throwable $previous = null)
    {
        parent::__construct(sprintf('The package %s is invalid.', $package), $code, $previous);
    }
}
