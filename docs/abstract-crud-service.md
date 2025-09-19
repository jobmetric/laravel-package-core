[Back To README.md](https://github.com/jobmetric/laravel-package-core/blob/master/README.md)

# Introduction to Abstract Crud Service

The `AbstractCrudService` is a reusable **base service class** for building CRUD services in Laravel.  
It standardizes querying, pagination, resource transformation, hooks, events, and response handling.  
This service is designed to reduce boilerplate code and provide a clean, consistent approach to managing models.

---

## Features

- **Query building** via [Spatie QueryBuilder](https://spatie.be/docs/laravel-query-builder)
- **Resource transformation** using Laravel API Resources
- **Transactions** for mutating operations (store, update, delete, restore, force delete)
- **Lifecycle hooks** (`beforeStore`, `afterUpdate`, etc.) to customize logic
- **Event dispatching** via configurable event classes
- **SoftDeletes support** with `restore()` and `forceDelete()` if enabled
- **Uniform responses** using `JobMetric\PackageCore\Output\Response`

---

## Class Properties

```php
protected string $entityName = '';             // Entity name used in response messages
protected Model $model;                        // Bound Eloquent model instance
protected string $resource;                    // API Resource class for transformations

protected bool $softDelete = false;            // Enable soft-deletes
protected bool $hasRestore = false;            // Enable restore() method
protected bool $hasForceDelete = false;        // Enable forceDelete() method

protected static string $modelClass;           // Fully qualified model class
protected static string $resourceClass;        // Fully qualified resource class

protected static array $fields = ['id'];       // Allowed fields for query/filter/sort
protected static array $defaultSort = ['-id']; // Default sort order

protected static ?string $storeEventClass = null;
protected static ?string $updateEventClass = null;
protected static ?string $deleteEventClass = null;
protected static ?string $restoreEventClass = null;
protected static ?string $forceDeleteEventClass = null;
```

---

## Usage Example

### 1. Define a Service for Your Model

```php
namespace App\Services;

use App\Models\Post;
use App\Http\Resources\PostResource;
use JobMetric\PackageCore\Services\AbstractCrudService;

class PostService extends AbstractCrudService
{
    protected string $entityName = 'Post';

    protected bool $softDelete = true;
    protected bool $hasRestore = true;
    protected bool $hasForceDelete = true;

    protected static string $modelClass = Post::class;
    protected static string $resourceClass = PostResource::class;

    protected static array $fields = ['id', 'title', 'status', 'created_at'];
    protected static array $defaultSort = ['-id'];
}
```

---

## Available Methods

### 2. `query()`
Builds a query builder with filters, sorts, and relations.

```php
$service = app(PostService::class);
$query = $service->query(['status' => 'published'], ['author']);
```

---

### 3. `paginate()`
Returns a paginated response with transformed resources.

```php
$response = $service->paginate(10, ['status' => 'published'], ['author']);
// Returns Response with PostResource collection
```

---

### 4. `all()`
Returns all results (without pagination).

```php
$response = $service->all(['status' => 'draft']);
// Response with PostResource collection of drafts
```

---

### 5. `show()`
Fetch a single record by ID.

```php
$response = $service->show(1, ['author', 'comments']);
// Response with single PostResource
```

---

### 6. `store()`
Create a new record.

```php
$data = [
    'title' => 'My First Post',
    'status' => 'published',
];

$response = $service->store($data);
// Response with created PostResource
```

---

### 7. `update()`
Update an existing record.

```php
$data = ['title' => 'Updated Post Title'];

$response = $service->update(1, $data);
// Response with updated PostResource
```

---

### 8. `destroy()`
Delete a record (soft-delete if enabled).

```php
$response = $service->destroy(1);
// Response with deleted PostResource snapshot
```

---

### 9. `restore()`
Restore a soft-deleted record.

```php
$response = $service->restore(1);
// Response with restored PostResource
```

---

### 10. `forceDelete()`
Permanently delete a soft-deleted record.

```php
$response = $service->forceDelete(1);
// Response with force-deleted PostResource snapshot
```

---

## Hooks

You can override lifecycle hooks to customize behavior:

```php
protected function beforeStore(Model $model, array &$data): void
{
    $data['created_by'] = auth()->id();
}

protected function afterUpdate(Model $model, array &$data): void
{
    activity()->performedOn($model)->log('Post updated');
}
```

Available hooks:
- `changeFieldStore()`
- `beforeStore()`, `afterStore()`
- `changeFieldUpdate()`
- `beforeUpdate()`, `afterUpdate()`
- `beforeDestroy()`, `afterDestroy()`
- `beforeRestore()`, `afterRestore()`
- `beforeForceDelete()`, `afterForceDelete()`

---

## Events

If event classes are configured, they are automatically dispatched after actions:

- `storeEventClass`
- `updateEventClass`
- `deleteEventClass`
- `restoreEventClass`
- `forceDeleteEventClass`

Example:

```php
protected static ?string $storeEventClass = \App\Events\PostStored::class;
```

---

## Response Format

All methods return a standardized `Response` object:

```php
Response::make(
    true, // success
    'Post created successfully', // message
    PostResource::make($post),   // data
    201 // HTTP status code
);
```

---

## Summary

The `AbstractCrudService` provides:
- A **standard CRUD pattern**
- Clean integration with **resources, hooks, and events**
- **SoftDeletes support**
- **Uniform Response** handling

This makes it easy to build maintainable, reusable service layers for your Laravel packages.
