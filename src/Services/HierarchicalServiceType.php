<?php

namespace JobMetric\PackageCore\Services;

/**
 * Trait HierarchicalServiceType
 *
 * @package JobMetric\PackageCore
 */
trait HierarchicalServiceType
{
    /**
     * Enable Hierarchical.
     *
     * @return static
     */
    public function hierarchical(): static
    {
        $this->setTypeParam('hierarchical', true);

        return $this;
    }

    /**
     * Has Hierarchical.
     *
     * @return bool
     */
    public function hasHierarchical(): bool
    {
        return $this->getTypeParam('hierarchical', false);
    }
}
