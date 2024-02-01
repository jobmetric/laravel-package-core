<?php

namespace JobMetric\PackageCore\Exceptions;

use Exception;
use Throwable;

class ArrayNotAssocException extends Exception
{
    public function __construct(int $code = 400, ?Throwable $previous = null)
    {
        parent::__construct('Array must be assoc keys', $code, $previous);
    }
}
