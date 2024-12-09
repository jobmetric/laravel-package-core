<?php

namespace JobMetric\PackageCore\Services;

/**
 * Trait ListExportServiceType
 *
 * @package JobMetric\PackageCore
 */
trait ListExportServiceType
{
    /**
     * Enable Export In List.
     *
     * @return static
     */
    public function exportInList(): static
    {
        $this->setTypeParam('exportInList', true);

        return $this;
    }

    /**
     * Has Export In List.
     *
     * @return bool
     */
    public function hasExportInList(): bool
    {
        return $this->getTypeParam('exportInList', false);
    }
}
