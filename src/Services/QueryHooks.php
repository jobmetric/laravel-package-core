<?php

namespace JobMetric\PackageCore\Services;

use Spatie\QueryBuilder\QueryBuilder;

/**
 * Trait QueryHooks
 *
 * Provides extension points for query configuration:
 * - allowedFields/Filters/Sorts: child services may override to expand base rules
 * - afterQuery: final modifier after the base query is built
 */
trait QueryHooks
{
    /**
     * Additional allowed fields for selection/filtering/sorting.
     *
     * @return string[]
     */
    protected function allowedFields(): array
    {
        return [];
    }

    /**
     * Additional allowed filters.
     *
     * @return string[]
     */
    protected function allowedFilters(): array
    {
        return [];
    }

    /**
     * Additional allowed sorts.
     *
     * @return string[]
     */
    protected function allowedSorts(): array
    {
        return [];
    }

    /**
     * Final hook to tweak the QueryBuilder instance after base setup.
     *
     * @param QueryBuilder $query
     * @param array $filters
     * @param array $with
     * @param string|null $mode
     *
     * @return void
     */
    protected function afterQuery(QueryBuilder &$query, array $filters = [], array $with = [], ?string $mode = null): void
    {
        // No-op by default.
    }
}
