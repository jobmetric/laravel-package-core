<?php

namespace JobMetric\PackageCore\Models;

use Illuminate\Database\Eloquent\Builder;

trait HasBooleanStatus
{
    /**
     * Scope active.
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    /**
     * Scope inactive.
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('status', false);
    }
}
