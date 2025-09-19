<?php

namespace JobMetric\PackageCore\Services;

use Illuminate\Database\Eloquent\Model;

/**
 * Trait HasHooks
 *
 * Lifecycle hooks to customize behavior per service without overriding core logic.
 * All hooks are optional; override in child service only when needed.
 */
trait HasHooks
{
    /**
     * Mutate/normalize payload before create.
     *
     * @param array $data
     *
     * @return void
     */
    protected function changeFieldStore(array &$data): void
    {
        // No-op by default.
    }

    /**
     * Runs just before model is persisted (create).
     *
     * @param Model $model
     * @param array $data
     *
     * @return void
     */
    protected function beforeStore(Model $model, array &$data): void
    {
        // No-op by default.
    }

    /**
     * Runs right after model is persisted (create).
     *
     * @param Model $model
     * @param array $data
     *
     * @return void
     */
    protected function afterStore(Model $model, array &$data): void
    {
        // No-op by default.
    }

    /**
     * Mutate/normalize payload before update.
     *
     * @param array $data
     *
     * @return void
     */
    protected function changeFieldUpdate(array &$data): void
    {
        // No-op by default.
    }

    /**
     * Runs just before model is persisted (update).
     *
     * @param Model $model
     * @param array $data
     *
     * @return void
     */
    protected function beforeUpdate(Model $model, array &$data): void
    {
        // No-op by default.
    }

    /**
     * Runs right after model is persisted (update).
     *
     * @param Model $model
     * @param array $data
     *
     * @return void
     */
    protected function afterUpdate(Model $model, array &$data): void
    {
        // No-op by default.
    }

    /**
     * Runs just before deletion.
     *
     * @param Model $model
     *
     * @return void
     */
    protected function beforeDestroy(Model $model): void
    {
        // No-op by default.
    }

    /**
     * Runs right after deletion.
     *
     * @param Model $model
     *
     * @return void
     */
    protected function afterDestroy(Model $model): void
    {
        // No-op by default.
    }

    /**
     * Runs just before restore.
     *
     * @param Model $model
     *
     * @return void
     */
    protected function beforeRestore(Model $model): void
    {
        // No-op by default.
    }

    /**
     * Runs right after restore.
     *
     * @param Model $model
     *
     * @return void
     */
    protected function afterRestore(Model $model): void
    {
        // No-op by default.
    }

    /**
     * Runs just before force delete.
     *
     * @param Model $model
     *
     * @return void
     */
    protected function beforeForceDelete(Model $model): void
    {
        // No-op by default.
    }

    /**
     * Runs right after force delete.
     *
     * @param Model $model
     *
     * @return void
     */
    protected function afterForceDelete(Model $model): void
    {
        // No-op by default.
    }
}
