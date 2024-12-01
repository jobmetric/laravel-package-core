<?php

namespace JobMetric\PackageCore\Services;

/**
 * Trait InformationServiceType
 *
 * @package JobMetric\PackageCore
 */
trait InformationServiceType
{
    /**
     * Set Label.
     *
     * @param string $label
     *
     * @return static
     */
    public function label(string $label): static
    {
        $this->setTypeParam('label', $label);

        return $this;
    }

    /**
     * Get Label
     *
     * @return string
     */
    public function getLabel(): string
    {
        return trans($this->getTypeParam('label', ''));
    }

    /**
     * Set Description.
     *
     * @param string $description
     *
     * @return static
     */
    public function description(string $description): static
    {
        $this->setTypeParam('description', $description);

        return $this;
    }

    /**
     * Get Description
     *
     * @return string
     */
    public function getDescription(): string
    {
        return trans($this->getTypeParam('description', ''));
    }
}
