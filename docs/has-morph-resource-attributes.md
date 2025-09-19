[Back To README.md](https://github.com/jobmetric/laravel-package-core/blob/master/README.md)

# Introduction to Dynamic Resources for MorphTo

**Namespace:** `JobMetric\PackageCore\Traits\HasMorphResourceAttributes`  
**Works with:** `JobMetric\PackageCore\Events\ResourceResolveEvent`  
**Goal:** Expose dynamic `<relation>_resource` attributes for **every `MorphTo` relation** on a model, resolved through a single global event — with **zero lazy-loading** and no per-relation accessors.

---

## Contents

1. [Overview](#overview)  
2. [Concepts](#concepts)  
3. [Quick Start](#quick-start)  
4. [API](#api)  
5. [Configuration](#configuration)  
6. [Autodiscovery & Excludes](#autodiscovery--excludes)  
7. [Attribute Name Mapping (snake⇄camel)](#attribute-name-mapping-snakecamel)  
8. [Eager-loading Patterns](#eager-loading-patterns)  
9. [Event Integration & Listeners](#event-integration--listeners)  
10. [End-to-End Examples](#end-to-end-examples)  
11. [Testing Recipes](#testing-recipes)  
12. [Performance & Pitfalls](#performance--pitfalls)  
13. [FAQ](#faq)

---

## Overview

**Problem:** For polymorphic relations (`MorphTo`), we often need a standardized “resource representation” (e.g., a `JsonResource`) without hard-wiring resource logic into each model.

**Solution:**  
- Use this trait to add **virtual attributes** like `<relation>_resource` (e.g., `slugable_resource`).  
- When accessed, the trait **dispatches** `ResourceResolveEvent` for the related instance.  
- **Listeners** decide which transformer/resource to use and set `$event->resource`.  
- **No lazy-loading** occurs inside the trait — it only returns a result if the relation is already loaded (N+1 safe).

---

## Concepts

- **Subject**: The related model instance (e.g., `Post`, `Product`) from a `MorphTo` relation.  
- **Context** (`string|null`): Where/how the resource will be used (e.g., `api.list`, `api.detail`, `web.card`, `admin.table`).  
- **Hints** (`array`): Fine-grained switches that guide listeners (e.g., `forbid_db`, `transformer`, `fields`, `locale`).  
- **Includes** (`array`): Relations the **caller has already eager-loaded** and expects resources to safely use with `whenLoaded()`.

---

## Quick Start

### 1) Add the trait to your model

```php
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use JobMetric\PackageCore\Traits\HasMorphResourceAttributes;

class Slug extends Model
{
    use HasMorphResourceAttributes;

    // Optional: explicit relations (unioned with autodiscovered ones)
    protected array $resourceMorphRelations = ['slugable'];

    // Optional defaults
    protected ?string $resourceMorphDefaultContext = 'api.list';
    protected array $resourceMorphDefaultHints = ['forbid_db' => true];

    // Optional per-relation includes (supports polymorphic map)
    protected array $resourceMorphIncludes = [
        'slugable' => [
            \App\Models\Post::class    => ['category'],
            \App\Models\Product::class => ['brand'],
        ],
    ];

    public function slugable(): MorphTo
    {
        return $this->morphTo();
    }
}
```

> Autodiscovery is **ON** by default. You can disable it with `protected bool $resourceMorphAutoDiscover = false;`.

### 2) Eager-load the relation(s)

```php
use Illuminate\Database\Eloquent\Relations\MorphTo;

$slugs = Slug::query()
    ->with([
        'slugable' => function (MorphTo $morphTo) {
            $morphTo->morphWith([
                \App\Models\Post::class    => ['category'],
                \App\Models\Product::class => ['brand'],
            ]);
        },
    ])
    ->latest()->get();
```

### 3) Use the dynamic attribute

```php
foreach ($slugs as $slug) {
    $resource = $slug->slugable_resource; // resolved via ResourceResolveEvent
}
```

> If `slugable` is **not loaded**, `slugable_resource` returns `null` (no event, no query).

---

## API

### Dynamic attribute

- **`$model->{<relation>_resource}`**  
  When accessed, the trait:
  1. Maps the `<relation>` token to the actual `MorphTo` method (supports snake/camel).
  2. If the relation **is loaded**, dispatches `ResourceResolveEvent`.
  3. Memoizes and returns `$event->resource` (or `null` if not handled).

### Method: manual resolution with overrides

```php
public function resolveMorphResource(
    string $relation,
    ?string $context = null,
    array $includes = [],
    array $hints = []
): mixed
```

Use this when you want to override `context/includes/hints` for a specific call.

---

## Configuration

Add any of these **optional** properties to your model:

| Property | Type | Default | Purpose |
|---|---|---:|---|
| `$resourceMorphRelations` | `string[]` | `[]` | Explicit list of `MorphTo` relations to expose as `*_resource`. |
| `$resourceMorphAutoDiscover` | `bool` | **`true`** | Discover public, parameterless methods returning `MorphTo` and **union** them with explicit ones. |
| `$resourceMorphDiscoveryExcept` | `string[]` | `[]` | Deny-list for autodiscovery (relation method names). |
| `$resourceMorphIncludes` | `array<string,mixed>` | `[]` | Per-relation includes: simple list or polymorphic map. |
| `$resourceMorphContexts` | `array<string,string>` | `[]` | Per-relation context, e.g. `['slugable' => 'api.list']`. |
| `$resourceMorphHintsByRelation` | `array<string,array<string,mixed>>` | `[]` | Per-relation hints merged with defaults and call-time overrides. |
| `$resourceMorphDefaultContext` | `?string` | `null` | Fallback context. |
| `$resourceMorphDefaultHints` | `array<string,mixed>` | `[]` | Hints merged into every resolution unless overridden. |

**Recommended defaults:**

```php
protected ?string $resourceMorphDefaultContext = 'api.list';
protected array $resourceMorphDefaultHints = ['forbid_db' => true];
```

---

## Autodiscovery & Excludes

- **Autodiscovery ON (default):** The trait scans public, parameterless methods that return a `MorphTo`.  
- **Exclude certain relations:**  
  ```php
  protected array $resourceMorphDiscoveryExcept = ['internalable'];
  ```

This gives you “it just works” convenience with the ability to **skip** specific relations you don’t want exposed.

---

## Attribute Name Mapping (snake⇄camel)

Dynamic attribute tokens support snake/camel equivalence. The trait matches in this order:

1. Exact method name  
2. `camel(token)` or `lcfirst(studly(token))` equals relation name  
3. `snake(token)` equals `snake(relation)`  
4. Relaxed comparison across the normalized forms

**Examples:**

| Attribute | Relation |
|---|---|
| `slugable_resource` | `slugable()` |
| `slugAble_resource` | `slugAble()` |
| `slug_able_resource` | `slugAble()` |
| `az_bs_resource` | `azBs()` |
| `azBs_resource` | `azBs()` |

---

## Eager-loading Patterns

### A) Query-time eager-load (preferred)

```php
use Illuminate\Database\Eloquent\Relations\MorphTo;

$slugs = Slug::query()
    ->with([
        'slugable' => function (MorphTo $morphTo) {
            $morphTo->morphWith([
                \App\Models\Post::class    => ['category', 'tags'],
                \App\Models\Product::class => ['brand'],
            ]);
        },
    ])
    ->get();
```

### B) After `get()` (collection-time) using `loadMorph`

```php
$slugs = Slug::with('slugable')->get();

$slugs->loadMorph('slugable', [
    \App\Models\Post::class    => ['category', 'tags'],
    \App\Models\Product::class => ['brand'],
]);
```

---

## Event Integration & Listeners

The trait dispatches:

```php
new \JobMetric\PackageCore\Events\ResourceResolveEvent(
    subject: $related,        // eager-loaded relation instance
    context: 'api.list',      // from per-relation/default/override
    hints: ['forbid_db' => true],
    includes: ['category']    // or polymorphic map
);
```

### Register listeners (EventServiceProvider)

```php
protected $listen = [
    \JobMetric\PackageCore\Events\ResourceResolveEvent::class => [
        \App\Listeners\ResolvePostResource::class,
        \App\Listeners\ResolveProductResource::class,
        // add more as needed
    ],
];
```

### Single-writer convention

- The **first** capable listener should set `$event->resource`.  
- All others should bail if `$event->isResolved()` is `true`.

---

## End-to-End Examples

### 1) Slug → Post/Product

**Model:**

```php
class Slug extends Model
{
    use \JobMetric\PackageCore\Traits\HasMorphResourceAttributes;

    protected array $resourceMorphRelations = ['slugable'];
    protected ?string $resourceMorphDefaultContext = 'api.list';
    protected array $resourceMorphDefaultHints = ['forbid_db' => true];

    protected array $resourceMorphIncludes = [
        'slugable' => [
            \App\Models\Post::class    => ['category'],
            \App\Models\Product::class => ['brand'],
        ],
    ];

    public function slugable(): \Illuminate\Database\Eloquent\Relations\MorphTo { return $this->morphTo(); }
}
```

**Resources:**

```php
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'       => $this->id,
            'title'    => $this->title,
            'slug'     => $this->slug,
            'category' => $this->whenLoaded('category', fn () => [
                'id'   => $this->category->id,
                'name' => $this->category->name,
            ]),
        ];
    }
}

class ProductResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'    => $this->id,
            'name'  => $this->name,
            'brand' => $this->whenLoaded('brand', fn () => [
                'id'   => $this->brand->id,
                'name' => $this->brand->name,
            ]),
        ];
    }
}
```

**Listeners:**

```php
use JobMetric\PackageCore\Events\ResourceResolveEvent;
use App\Models\Post;
use App\Models\Product;

final class ResolvePostResource
{
    public function handle(ResourceResolveEvent $event): void
    {
        if ($event->isResolved() || !$event->subject instanceof Post) {
            return;
        }

        // Honor hints (optional)
        $forbid = (bool) ($event->hints['forbid_db'] ?? false);
        if ($forbid) {
            // don't lazy-load; rely on includes & whenLoaded()
        }

        $resourceClass = $event->hints['transformer'] ?? \App\Http\Resources\PostResource::class;
        $event->setResource(app($resourceClass, ['resource' => $event->subject]));
    }
}

final class ResolveProductResource
{
    public function handle(ResourceResolveEvent $event): void
    {
        if ($event->isResolved() || !$event->subject instanceof Product) {
            return;
        }

        $event->setResource(new \App\Http\Resources\ProductResource($event->subject));
    }
}
```

**Query & usage:**

```php
use Illuminate\Database\Eloquent\Relations\MorphTo;

$slugs = Slug::query()
    ->with([
        'slugable' => function (MorphTo $morphTo) {
            $morphTo->morphWith([
                \App\Models\Post::class    => ['category'],
                \App\Models\Product::class => ['brand'],
            ]);
        },
    ])->get();

return $slugs->map->slugable_resource; // collection of resources (no extra queries)
```

---

### 2) Multiple morphs with per-relation configs

**Model:**

```php
class Activity extends Model
{
    use \JobMetric\PackageCore\Traits\HasMorphResourceAttributes;

    protected array $resourceMorphRelations = ['subjectable','actorable'];
    protected ?string $resourceMorphDefaultContext = 'admin.row';
    protected array $resourceMorphDefaultHints = ['forbid_db' => true];

    protected array $resourceMorphIncludes = [
        'subjectable' => [\App\Models\Post::class => ['category']],
        'actorable'   => [\App\Models\User::class => []],
    ];

    protected array $resourceMorphContexts = [
        'subjectable' => 'admin.detail',
        'actorable'   => 'admin.row',
    ];

    public function subjectable(): \Illuminate\Database\Eloquent\Relations\MorphTo { return $this->morphTo(); }
    public function actorable(): \Illuminate\Database\Eloquent\Relations\MorphTo { return $this->morphTo(); }
}
```

**Usage:**

```php
$items = Activity::with(['subjectable','actorable'])->latest()->take(20)->get();

foreach ($items as $a) {
    $subject = $a->subjectable_resource; // honors 'admin.detail' context
    $actor   = $a->actorable_resource;   // honors 'admin.row' context
}
```

---

### 3) Call-time overrides

```php
$slug = Slug::with('slugable')->first();

$res = $slug->resolveMorphResource(
    relation: 'slugable',
    context: 'api.detail.private',
    includes: [\App\Models\Post::class => ['category', 'tags']],
    hints: ['forbid_db' => true, 'transformer' => \App\Http\Resources\PostSummaryResource::class]
);
```

---

## Testing Recipes

### A) Dynamic mapping (snake→camel)

```php
public function test_snake_token_maps_to_camel_relation(): void
{
    $m = new class extends \Illuminate\Database\Eloquent\Model {
        use \JobMetric\PackageCore\Traits\HasMorphResourceAttributes;
        protected $table = 'x';
        protected array $resourceMorphRelations = ['azBs'];
        public function azBs(): \Illuminate\Database\Eloquent\Relations\MorphTo { return $this->morphTo(); }

        // override to avoid event bus in unit test
        public function resolveMorphResource(string $relation, ?string $context = null, array $includes = [], array $hints = []): mixed
        {
            return "RESOLVED:{$relation}";
        }
    };

    // simulate eager-loaded relation
    $m->setRelation('azBs', new \stdClass());

    $this->assertSame('RESOLVED:azBs', $m->az_bs_resource);
    $this->assertSame('RESOLVED:azBs', $m->azBs_resource);
}
```

### B) No extra queries with `forbid_db`

```php
DB::enableQueryLog();

$slug = Slug::with(['slugable' => function (MorphTo $m) {
    $m->morphWith([\App\Models\Post::class => ['category']]);
}])->first();

$resource = $slug->slugable_resource;

$queries = collect(DB::getQueryLog());
$this->assertTrue($queries->isNotEmpty()); // initial eager-load present
// Optionally assert no extra queries during resolution depending on your listener policy
```

### C) Event registration sanity

```php
Event::fake();
$slug = Slug::with('slugable')->first();
$slug->slugable_resource;
Event::assertDispatched(\JobMetric\PackageCore\Events\ResourceResolveEvent::class);
```

---

## Performance & Pitfalls

**Do:**
- Eager-load requested includes (`with`, `morphWith`, `loadMorph`).
- Keep listeners **synchronous**; return immediately if `$event->isResolved()` is true.
- Use `hints['forbid_db' => true]` for list contexts to enforce no new queries.
- Enable `Model::preventLazyLoading()` in dev to catch accidental lazy loads.

**Don’t:**
- Don’t queue listeners — the attribute needs the result immediately.
- Don’t lazy-load inside listeners for collection views (unless you **explicitly** allow it).
- Don’t rely on ambiguous context names — use clear dot-notation (`api.list`, `api.detail`, etc.).

---

## FAQ

**Q: What types can `$event->resource` be?**  
A: `JsonResource`, `Arrayable`, `JsonSerializable`, plain `array`, `string`, or `null`.

**Q: What if no listener handles the subject?**  
A: The attribute returns `null`. You can add a fallback listener if desired.

**Q: Can I switch transformers at runtime?**  
A: Yes. Pass `hints['transformer'] = MyResource::class` and honor it in your listener.

**Q: Does the trait ever query the DB?**  
A: No. It **never** lazy-loads. If the relation isn’t loaded, it returns `null` without dispatching the event.

---

_Use and adapt freely within your application or package._

- [Next To Abstract CRUD Service](https://github.com/jobmetric/laravel-package-core/blob/master/docs/abstract-crud-service.md)
