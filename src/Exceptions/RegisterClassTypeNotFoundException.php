<?php

namespace JobMetric\PackageCore\Exceptions;

use Exception;
use Throwable;

class RegisterClassTypeNotFoundException extends Exception
{
    public function __construct(string $type, int $code = 400, ?Throwable $previous = null)
    {
        parent::__construct(sprintf('The register class type %s not found.', $type), $code, $previous);
    }
}
