<?php

namespace JobMetric\PackageCore\Exceptions;

use Exception;
use Throwable;

class ConsoleKernelFileNotFoundException extends Exception
{
    public function __construct(string $package, int $code = 400, ?Throwable $previous = null)
    {
        parent::__construct("The ConsoleKernel.php file not found in package $package.", $code, $previous);
    }
}
