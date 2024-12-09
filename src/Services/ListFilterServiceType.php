<?php

namespace JobMetric\PackageCore\Services;

/**
 * Trait ListFilterServiceType
 *
 * @package JobMetric\PackageCore
 */
trait ListFilterServiceType
{
    /**
     * Enable Remove Filter In List.
     *
     * @return static
     */
    public function removeFilterInList(): static
    {
        $this->setTypeParam('removeFilterInList', false);

        return $this;
    }

    /**
     * Has Remove Filter In List.
     *
     * @return bool
     */
    public function hasRemoveFilterInList(): bool
    {
        return $this->getTypeParam('removeFilterInList', true);
    }
}
