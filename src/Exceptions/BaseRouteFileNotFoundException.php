<?php

namespace JobMetric\PackageCore\Exceptions;

use Exception;
use Throwable;

class BaseRouteFileNotFoundException extends Exception
{
    public function __construct(string $package, int $code = 400, ?Throwable $previous = null)
    {
        parent::__construct("The base route file $package not found.", $code, $previous);

    }
}
