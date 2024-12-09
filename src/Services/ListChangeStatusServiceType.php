<?php

namespace JobMetric\PackageCore\Services;

/**
 * Trait ListChangeStatusServiceType
 *
 * @package JobMetric\PackageCore
 */
trait ListChangeStatusServiceType
{
    /**
     * Enable Change Status In List.
     *
     * @return static
     */
    public function changeStatusInList(): static
    {
        $this->setTypeParam('changeStatusInList', true);

        return $this;
    }

    /**
     * Has Change Status In List.
     *
     * @return bool
     */
    public function hasChangeStatusInList(): bool
    {
        return $this->getTypeParam('changeStatusInList', false);
    }
}
