<?php

namespace JobMetric\PackageCore\Services;

/**
 * Trait ListImportServiceType
 *
 * @package JobMetric\PackageCore
 */
trait ListImportServiceType
{
    /**
     * Enable Import In List.
     *
     * @return static
     */
    public function importInList(): static
    {
        $this->setTypeParam('importInList', true);

        return $this;
    }

    /**
     * Has Import In List.
     *
     * @return bool
     */
    public function hasImportInList(): bool
    {
        return $this->getTypeParam('importInList', false);
    }
}
