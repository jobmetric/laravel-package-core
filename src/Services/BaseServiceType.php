<?php

namespace JobMetric\PackageCore\Services;

use JobMetric\PackageCore\Exceptions\ServiceTypeTypeNotMatchException;
use Throwable;

/**
 * Trait BaseServiceType
 *
 * @package JobMetric\PackageCore
 */
trait BaseServiceType
{
    /**
     * Get Types
     *
     * @return array
     */
    public function getTypes(): array
    {
        return array_keys($this->getInContainer());
    }

    /**
     * Has type in types
     *
     * @param string|null $type
     *
     * @return bool
     */
    public function hasType(string|null $type): bool
    {
        return in_array($type, $this->getTypes());
    }

    /**
     * Check type in types
     *
     * @param string|null $type
     *
     * @return void
     * @throws Throwable
     */
    public function checkType(string|null $type): void
    {
        if (!$this->hasType($type)) {
            throw new ServiceTypeTypeNotMatchException(static::class, $type);
        }
    }
}
