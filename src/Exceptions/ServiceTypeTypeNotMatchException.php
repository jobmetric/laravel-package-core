<?php

namespace JobMetric\PackageCore\Exceptions;

use Exception;
use Throwable;

class ServiceTypeTypeNotMatchException extends Exception
{
    public function __construct(string $service, string $type, int $code = 400, ?Throwable $previous = null)
    {
        parent::__construct("Type [$type] is not match in service [$service].", $code, $previous);
    }
}
