<?php

namespace JobMetric\PackageCore\Models;

use Illuminate\Database\Eloquent\Builder;

/**
 * Trait HasBooleanStatus
 *
 * Provides common query scopes for models with a `status` boolean column.
 * Useful for enabling/disabling models or filtering based on their active state.
 *
 * This trait assumes the presence of a `status` column in the corresponding database table,
 * where `true` indicates an active/enabled state and `false` indicates an inactive/disabled state.
 *
 * # Example Usage:
 * ```php
 * Model::active()->get();   // returns only rows where status = true
 * Model::inactive()->get(); // returns only rows where status = false
 * ```
 *
 * # Recommended Use:
 * Add this trait to any Eloquent model where a boolean `status` column
 * is used to represent active/inactive state.
 *
 * @method static Builder active()    Scope a query to only include active records.
 * @method static Builder inactive()  Scope a query to only include inactive records.
 *
 * @package JobMetric\PackageCore\Models
 */
trait HasBooleanStatus
{
    /**
     * Scope a query to only include active (status = true) models.
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
     * Scope a query to only include inactive (status = false) models.
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
