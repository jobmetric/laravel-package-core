<?php

namespace JobMetric\PackageCore\Services;

/**
 * Trait ListShowDescriptionServiceType
 *
 * @package JobMetric\PackageCore
 */
trait ListShowDescriptionServiceType
{
    /**
     * Enable Show Description In List.
     *
     * @return static
     */
    public function showDescriptionInList(): static
    {
        $this->setTypeParam('showDescriptionInList', true);

        return $this;
    }

    /**
     * Has Show Description In List.
     *
     * @return bool
     */
    public function hasShowDescriptionInList(): bool
    {
        return $this->getTypeParam('showDescriptionInList', false);
    }
}
