<?php

namespace JobMetric\PackageCore\Services;

use BadMethodCallException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use JobMetric\PackageCore\Output\Response;
use Spatie\QueryBuilder\QueryBuilder;
use Throwable;

/**
 * Class AbstractCrudService
 *
 * A reusable, clean-code oriented CRUD base service that standardizes:
 * - Query building via Spatie\QueryBuilder (allowed fields/filters/sorts + default sort)
 * - Resource transformation (single and collection)
 * - Transactions for mutating operations
 * - Lifecycle hooks (before/after create/update/delete/restore/forceDelete)
 * - Optional event dispatching via class-string properties
 * - Uniform output using JobMetric PackageCore Response
 *
 * Feature flags:
 * - $softDelete: enable soft-deletion semantics for this service
 * - $hasRestore: expose restore() operation (requires soft-deletes)
 * - $hasForceDelete: expose forceDelete() operation (requires soft-deletes)
 */
abstract class AbstractCrudService
{
    use HasHooks,
        HasEvents,
        QueryHooks;

    /**
     * Human-readable entity name used in response messages (e.g., "Flow", "Order").
     *
     * @var string
     */
    protected string $entityName = '';

    /**
     * Resolved Eloquent model instance operated by this service.
     *
     * @var Model
     */
    protected Model $model;

    /**
     * Laravel API Resource FQCN used to transform single items and collections.
     *
     * @var class-string
     */
    protected string $resource;

    /**
     * List of disabled public API methods for this service.
     *
     * @var string[]
     */
    protected array $excepts = [];

    /**
     * Fully-qualified Eloquent model class name handled by the service.
     *
     * @var class-string
     */
    protected static string $modelClass;

    /**
     * Fully-qualified Laravel API Resource class name used by the service.
     *
     * @var class-string
     */
    protected static string $resourceClass;

    /**
     * Allowed fields for selection, filtering, and sorting.
     *
     * @var string[]
     */
    protected static array $fields = ['id'];

    /**
     * Default sort applied by QueryBuilder (e.g., ['-id']).
     *
     * @var string[]
     */
    protected static array $defaultSort = ['-id'];

    /**
     * Indicates the service is designed to use soft-deletion semantics.
     * If true, delete()/restore()/forceDelete() features can be exposed per flags.
     *
     * @var bool
     */
    protected bool $softDelete = false;

    /**
     * Indicates restore() should be exposed by this service.
     * Requires $softDelete = true and model using SoftDeletes.
     *
     * @var bool
     */
    protected bool $hasRestore = false;

    /**
     * Indicates forceDelete() should be exposed by this service.
     * Requires $softDelete = true and model using SoftDeletes.
     *
     * @var bool
     */
    protected bool $hasForceDelete = false;

    /**
     * Indicates toggleStatus() should be exposed by this service.
     * Requires model to have a 'status' boolean field.
     *
     * @var bool
     */
    protected bool $hasToggleStatus = false;

    /**
     * Event class name (FQCN) dispatched after storing a model.
     *
     * @var class-string|null
     */
    protected static ?string $storeEventClass = null;

    /**
     * Event class name (FQCN) dispatched after updating a model.
     *
     * @var class-string|null
     */
    protected static ?string $updateEventClass = null;

    /**
     * Event class name (FQCN) dispatched after deleting a model.
     *
     * @var class-string|null
     */
    protected static ?string $deleteEventClass = null;

    /**
     * Event class name (FQCN) dispatched after restoring a model.
     *
     * @var class-string|null
     */
    protected static ?string $restoreEventClass = null;

    /**
     * Event class name (FQCN) dispatched after force deleting a model.
     *
     * @var class-string|null
     */
    protected static ?string $forceDeleteEventClass = null;

    /**
     * Construct the service by resolving model and resource classes from the container.
     */
    public function __construct()
    {
        /** @var Model $model */
        $model = app(static::$modelClass);

        $this->model = $model;
        $this->resource = static::$resourceClass;
    }

    /**
     * Magic router that exposes a minimal public API, supports "do{Studly}" overrides,
     * and honors the $excepts list and soft-delete feature flags.
     *
     * @param string $name Method name being called.
     * @param array $arguments Arguments passed to the method.
     *
     * @return mixed
     *
     * @throws BadMethodCallException If the method is not available or not implemented.
     */
    public function __call(string $name, array $arguments)
    {
        $methods = ['query', 'paginate', 'all', 'show', 'store', 'update', 'destroy'];

        if ($this->softDelete || $this->hasRestore) {
            $methods[] = 'restore';
        }

        if ($this->softDelete || $this->hasForceDelete) {
            $methods[] = 'forceDelete';
        }

        if ($this->hasToggleStatus) {
            $methods[] = 'toggleStatus';
        }

        $methods = array_values(array_diff($methods, $this->excepts));

        if (!in_array($name, $methods, true)) {
            throw new BadMethodCallException("Method {$name} is not available in this service.");
        }

        foreach (['do' . Str::studly($name), $name] as $method) {
            if (method_exists($this, $method) && is_callable([$this, $method])) {
                return $this->{$method}(...$arguments);
            }
        }

        throw new BadMethodCallException("Method {$name} does not exist.");
    }

    /**
     * Build a Spatie QueryBuilder with allowed fields/filters/sorts and optional soft-delete mode.
     *
     * @param array $filters Associative filters applied to the query (->where()).
     * @param array $with Eager-loaded relations.
     * @param string|null $mode Soft-delete scope: 'withTrashed'|'onlyTrashed'|null.
     *
     * @return QueryBuilder Configured query builder.
     */
    public function query(array $filters = [], array $with = [], ?string $mode = null): QueryBuilder
    {
        $qb = QueryBuilder::for(get_class($this->model));

        if ($this->modelSupportsSoftDeletes()) {
            if ($mode === 'withTrashed') {
                $qb->withTrashed();
            }

            if ($mode === 'onlyTrashed') {
                $qb->onlyTrashed();
            }
        }

        $qb->allowedFields(array_merge(static::$fields, $this->allowedFields()))
            ->allowedSorts(array_merge(static::$fields, $this->allowedSorts()))
            ->allowedFilters(array_merge(static::$fields, $this->allowedFilters()))
            ->defaultSort(static::$defaultSort)
            ->where($filters);

        $this->afterQuery($qb, $filters, $with, $mode);

        if (!empty($with)) {
            $qb->with($with);
        }

        return $qb;
    }

    /**
     * Paginate and return a uniform Response wrapping a resource collection.
     *
     * @param int $pageLimit Items per page.
     * @param array $filters Filters for the query.
     * @param array $with Eager-loaded relations.
     * @param string|null $mode Soft-delete scope: 'withTrashed'|'onlyTrashed'|null.
     *
     * @return Response Standardized response with resource collection.
     */
    protected function paginate(int $pageLimit = 15, array $filters = [], array $with = [], ?string $mode = null): Response
    {
        $paginator = $this->query($filters, $with, $mode)->paginate($pageLimit);

        $resources = $this->resource::collection($paginator);

        $additional = $this->additionalForIndex($filters, $with, $mode);
        if (!is_null($additional)) {
            $resources = $resources->additional($additional);
        }

        return Response::make(true, null, $resources);
    }

    /**
     * Fetch all records and return a uniform Response wrapping a resource collection.
     *
     * @param array $filters Filters for the query.
     * @param array $with Eager-loaded relations.
     * @param string|null $mode Soft-delete scope: 'withTrashed'|'onlyTrashed'|null.
     *
     * @return Response Standardized response with resource collection.
     */
    protected function all(array $filters = [], array $with = [], ?string $mode = null): Response
    {
        $items = $this->query($filters, $with, $mode)->get();

        $resources = $this->resource::collection($items);

        $additional = $this->additionalForIndex($filters, $with, $mode);
        if (!is_null($additional)) {
            $resources = $resources->additional($additional);
        }

        return Response::make(true, null, $resources);
    }

    /**
     * Retrieve a single record by primary key and return a uniform Response.
     *
     * @param int $id Primary key of the record.
     * @param array $with Eager-loaded relations.
     * @param string|null $mode Soft-delete scope: 'withTrashed'|'onlyTrashed'|null.
     *
     * @return Response Standardized response with single resource.
     */
    protected function show(int $id, array $with = [], ?string $mode = null): Response
    {
        $builder = $this->model->newQuery();

        if ($this->modelSupportsSoftDeletes()) {
            if ($mode === 'withTrashed') {
                $builder->withTrashed();
            }

            if ($mode === 'onlyTrashed') {
                $builder->onlyTrashed();
            }
        }

        $model = $builder->with($with)->findOrFail($id);

        $resourceInstance = $this->resource::make($model);

        $additional = $this->additionalForShow($model);
        if (!is_null($additional)) {
            $resourceInstance = $resourceInstance->additional($additional);
        }

        return Response::make(true, null, $resourceInstance);
    }

    /**
     * Create and persist a new record, firing hooks and events, then return a uniform Response.
     *
     * @param array $data Payload for creation.
     * @param array $with Eager-loaded relations after save.
     *
     * @return Response Standardized response with created resource.
     * @throws Throwable
     */
    protected function store(array $data, array $with = []): Response
    {
        return DB::transaction(function () use ($data, $with) {
            $model = $this->model->newInstance();

            $this->changeFieldStore($data);

            $model->fill($data);

            $this->beforeCommon('store', $model, $data);
            $this->beforeStore($model, $data);
            $model->save();
            $this->afterStore($model, $data);
            $this->afterCommon('store', $model, $data);

            $this->fireStoreEvent($model, $data);

            $resourceInstance = $this->resource::make($model->load($with));

            $additional = $this->additionalForMutation($model, $data, 'store');
            if (!is_null($additional)) {
                $resourceInstance = $resourceInstance->additional($additional);
            }

            return Response::make(true, trans('package-core::base.messages.created', [
                'entity' => trans($this->entityName)
            ]), $resourceInstance, 201);
        });
    }

    /**
     * Update and persist an existing record, firing hooks and events, then return a uniform Response.
     *
     * @param int $id Primary key of the record to update.
     * @param array $data Payload for update.
     * @param array $with Eager-loaded relations after save.
     *
     * @return Response Standardized response with updated resource.
     * @throws Throwable
     */
    protected function update(int $id, array $data, array $with = []): Response
    {
        $model = $this->model->newQuery()->findOrFail($id);

        return DB::transaction(function () use ($model, $data, $with) {
            $this->changeFieldUpdate($model, $data);

            $model->fill($data);

            $this->beforeCommon('update', $model, $data);
            $this->beforeUpdate($model, $data);
            $model->save();
            $this->afterUpdate($model, $data);
            $this->afterCommon('update', $model, $data);

            $this->fireUpdateEvent($model, $data);

            $resourceInstance = $this->resource::make($model->load($with));

            $additional = $this->additionalForMutation($model, $data, 'update');
            if (!is_null($additional)) {
                $resourceInstance = $resourceInstance->additional($additional);
            }

            return Response::make(true, trans('package-core::base.messages.updated', [
                'entity' => trans($this->entityName)
            ]), $resourceInstance);
        });
    }

    /**
     * Delete a record (soft or hard per model behavior) and return a uniform Response.
     *
     * @param int $id Primary key of the record to delete.
     * @param array $with Eager-loaded relations prior to deletion (for payload snapshot).
     *
     * @return Response Standardized response with deleted resource snapshot.
     * @throws Throwable
     */
    protected function destroy(int $id, array $with = []): Response
    {
        $query = $this->model->newQuery()->with($with);
        $model = $query->findOrFail($id);

        $payload = $this->resource::make($model);

        $additional = $this->additionalForMutation($model, [], 'destroy');
        if (!is_null($additional)) {
            $payload = $payload->additional($additional);
        }

        return DB::transaction(function () use ($model, $payload) {
            $this->beforeCommon('destroy', $model, []);
            $this->beforeDestroy($model);
            $model->delete();
            $this->afterDestroy($model);
            $this->afterCommon('destroy', $model, []);

            $this->fireDeleteEvent($model);

            return Response::make(true, trans('package-core::base.messages.deleted', [
                'entity' => trans($this->entityName)
            ]), $payload);
        });
    }

    /**
     * Restore a soft-deleted record if enabled and supported, then return a uniform Response.
     *
     * @param int $id Primary key of the record to restore.
     * @param array $with Eager-loaded relations after restoration.
     *
     * @return Response Standardized response with restored resource.
     * @throws Throwable
     */
    protected function restore(int $id, array $with = []): Response
    {
        if (!$this->hasRestore && !$this->softDelete) {
            throw new BadMethodCallException('Restore operation is not enabled for this service.');
        }

        if (!$this->modelSupportsSoftDeletes()) {
            throw new BadMethodCallException('Restore operation requires a model with SoftDeletes.');
        }

        $model = $this->model->newQuery()->onlyTrashed()->with($with)->findOrFail($id);

        return DB::transaction(function () use ($model) {
            $this->beforeCommon('restore', $model, []);
            $this->beforeRestore($model);
            $model->restore();
            $this->afterRestore($model);
            $this->afterCommon('restore', $model, []);

            $this->fireRestoreEvent($model);

            $resourceInstance = $this->resource::make($model);

            $additional = $this->additionalForMutation($model, [], 'restore');
            if (!is_null($additional)) {
                $resourceInstance = $resourceInstance->additional($additional);
            }

            return Response::make(true, trans('package-core::base.messages.restored', [
                'entity' => trans($this->entityName)
            ]), $resourceInstance);
        });
    }

    /**
     * Permanently delete a soft-deleted record if enabled and supported, then return a uniform Response.
     *
     * @param int $id Primary key of the record to force delete.
     * @param array $with Eager-loaded relations prior to force deletion (for payload snapshot).
     *
     * @return Response Standardized response with force-deleted resource snapshot.
     * @throws Throwable
     */
    protected function forceDelete(int $id, array $with = []): Response
    {
        if (!$this->hasForceDelete && !$this->softDelete) {
            throw new BadMethodCallException('Force delete operation is not enabled for this service.');
        }

        if (!$this->modelSupportsSoftDeletes()) {
            throw new BadMethodCallException('Force delete operation requires a model with SoftDeletes.');
        }

        $model = $this->model->newQuery()->onlyTrashed()->with($with)->findOrFail($id);
        $payload = $this->resource::make($model);

        $additional = $this->additionalForMutation($model, [], 'forceDelete');
        if (!is_null($additional)) {
            $payload = $payload->additional($additional);
        }

        return DB::transaction(function () use ($model, $payload) {
            $this->beforeCommon('forceDelete', $model, []);
            $this->beforeForceDelete($model);
            $model->forceDelete();
            $this->afterForceDelete($model);
            $this->afterCommon('forceDelete', $model, []);

            $this->fireForceDeleteEvent($model);

            return Response::make(true, trans('package-core::base.messages.permanently_deleted', [
                'entity' => trans($this->entityName)
            ]), $payload);
        });
    }

    /**
     * Check if the bound model uses the SoftDeletes trait.
     *
     * @return bool True when the model uses SoftDeletes; otherwise false.
     */
    protected function modelSupportsSoftDeletes(): bool
    {
        return in_array(SoftDeletes::class, class_uses_recursive(get_class($this->model)), true);
    }

    /**
     * Toggle the boolean 'status' field for a given model.
     *
     * Role: quick enable/disable switch for models with a status field.
     * Checks if the model has a 'status' attribute before toggling.
     *
     * @param int $id Primary key of the record.
     * @param array<int,string> $with Eager-loaded relations after save.
     *
     * @return Response Standardized response with updated resource.
     * @throws Throwable
     */
    public function toggleStatus(int $id, array $with = []): Response
    {
        return DB::transaction(function () use ($id, $with) {
            $model = $this->model->newQuery()->findOrFail($id);

            // Check if model has status attribute
            // We check both getAttributes() (for loaded attributes) and hasAttribute() (for all possible attributes)
            $attributes = $model->getAttributes();
            $hasStatus = array_key_exists('status', $attributes) || 
                       (method_exists($model, 'hasAttribute') && $model->hasAttribute('status')) ||
                       in_array('status', $model->getFillable(), true) ||
                       property_exists($model, 'status');

            if (!$hasStatus) {
                throw new BadMethodCallException('Model does not have a status field.');
            }

            $model->status = ! $model->status;
            $model->save();

            $this->afterCommon('toggleStatus', $model, []);

            $resourceInstance = $this->resource::make($model->load($with));

            $additional = $this->additionalForMutation($model, [], 'toggleStatus');
            if (!is_null($additional)) {
                $resourceInstance = $resourceInstance->additional($additional);
            }

            return Response::make(true, trans('package-core::base.messages.status_toggled', [
                'entity' => trans($this->entityName),
            ]), $resourceInstance);
        });
    }
}
