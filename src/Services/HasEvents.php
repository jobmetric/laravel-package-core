<?php

namespace JobMetric\PackageCore\Services;

use Illuminate\Database\Eloquent\Model;

/**
 * Trait HasEvents
 *
 * Dispatches lifecycle events defined by class-string properties on the service:
 *  - static::$storeEventClass
 *  - static::$updateEventClass
 *  - static::$deleteEventClass
 *  - static::$restoreEventClass
 *  - static::$forceDeleteEventClass
 *
 * Each event class should exist and match the expected constructor signature:
 *  - store/update: __construct(Model $model, array $data)
 *  - delete/restore/forceDelete: __construct(Model $model)
 */
trait HasEvents
{
    /**
     * Dispatch "stored" event if configured.
     *
     * @param Model $model
     * @param array $data
     *
     * @return void
     */
    protected function fireStoreEvent(Model $model, array $data): void
    {
        if (isset(static::$storeEventClass) && static::$storeEventClass && class_exists(static::$storeEventClass)) {
            event(new static::$storeEventClass($model, $data));
        }
    }

    /**
     * Dispatch "updated" event if configured.
     *
     * @param Model $model
     * @param array $data
     *
     * @return void
     */
    protected function fireUpdateEvent(Model $model, array $data): void
    {
        if (isset(static::$updateEventClass) && static::$updateEventClass && class_exists(static::$updateEventClass)) {
            event(new static::$updateEventClass($model, $data));
        }
    }

    /**
     * Dispatch "deleted" event if configured.
     *
     * @param Model $model
     *
     * @return void
     */
    protected function fireDeleteEvent(Model $model): void
    {
        if (isset(static::$deleteEventClass) && static::$deleteEventClass && class_exists(static::$deleteEventClass)) {
            event(new static::$deleteEventClass($model));
        }
    }

    /**
     * Dispatch "restored" event if configured.
     *
     * @param Model $model
     *
     * @return void
     */
    protected function fireRestoreEvent(Model $model): void
    {
        if (isset(static::$restoreEventClass) && static::$restoreEventClass && class_exists(static::$restoreEventClass)) {
            event(new static::$restoreEventClass($model));
        }
    }

    /**
     * Dispatch "force-deleted" event if configured.
     *
     * @param Model $model
     *
     * @return void
     */
    protected function fireForceDeleteEvent(Model $model): void
    {
        if (isset(static::$forceDeleteEventClass) && static::$forceDeleteEventClass && class_exists(static::$forceDeleteEventClass)) {
            event(new static::$forceDeleteEventClass($model));
        }
    }
}
