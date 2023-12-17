<?php

namespace JobMetric\PackageCore\Exceptions;

use Exception;
use Throwable;

class MigrationFolderNotFoundException extends Exception
{
    public function __construct(string $package, int $code = 400, ?Throwable $previous = null)
    {
        parent::__construct("Migration folder not found in package $package.", $code, $previous);
    }
}
