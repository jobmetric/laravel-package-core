<?php

namespace JobMetric\PackageCore\Exceptions;

use Exception;
use Throwable;

class RegisterClassTypeNotFoundException extends Exception
{
    public function __construct(string $type, int $code = 400, ?Throwable $previous = null)
    {
        parent::__construct("The register class type $type not found.", $code, $previous);
    }
}
