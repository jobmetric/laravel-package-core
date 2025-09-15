[Back To README.md](https://github.com/jobmetric/laravel-package-core/blob/master/README.md)

# Introduction to ResourceResolveEvent — Global Resource Resolution Event

**Namespace:** `JobMetric\PackageCore\Events\ResourceResolveEvent`  
**Purpose:** A single, package‑agnostic event for resolving a “resource” (e.g., `JsonResource`, array, DTO) for **any** subject (Eloquent model, DTO, etc.) with full control over *where it will be used* (`context`) and *how it should be shaped* (`hints` and `includes`).

---

## TL;DR (Quick Start)

1. **Dispatch the event** with your subject and optional `context`, `includes`, and `hints`:
   ```php
   use JobMetric\PackageCore\Events\ResourceResolveEvent;

   $event = new ResourceResolveEvent(
       subject: $post,                  // Eloquent model or any object
       context: 'api.detail',           // where/how it will be used
       hints: ['forbid_db' => true],    // fine-grained knobs
       includes: ['category']           // relations you expect are eager-loaded
   );
   event($event);

   return $event->resource;             // JsonResource|array|string|null
   ```

2. **Write a listener** for your subject type(s):
   ```php
   use JobMetric\PackageCore\Events\ResourceResolveEvent;
   use App\Http\Resources\PostResource;
   use App\Models\Post;

   class ResolvePostResource
   {
       public function handle(ResourceResolveEvent $event): void
       {
           if ($event->isResolved() || !$event->subject instanceof Post) {
               return;
           }

           // Make sure we don't trigger extra queries (respect 'forbid_db')
           if (($event->hints['forbid_db'] ?? false) && ! $event->subject->relationLoaded('category')) {
               // We won't lazy-load here; resource will still honor whenLoaded('category')
           }

           $event->setResource(new PostResource($event->subject));
       }
   }
   ```

3. **Register the listener** in your `EventServiceProvider`:
   ```php
   protected $listen = [
       \JobMetric\PackageCore\Events\ResourceResolveEvent::class => [
           \App\Listeners\ResolvePostResource::class,
       ],
   ];
   ```

> **Rule of thumb**: Keep listeners **synchronous** and **single‑writer** (first capable listener sets the resource; others must bail). Avoid lazy loads in lists; use `includes` to eager-load from the caller.

---

## Why this event?

- **Decoupling**: Models don’t need to know which resource/transformer renders them; that’s the listener’s job.
- **Extensibility**: New modules/packages add listeners without patching models.
- **Determinism**: `includes` expresses what’s already loaded; listeners can safely use `whenLoaded()`.
- **Context‑aware**: `context` allows the same subject to be rendered differently (API vs Admin, list vs detail).

---

## API Reference

### Class
```php
namespace JobMetric\PackageCore\Events;

class ResourceResolveEvent
{
    public mixed $subject;
    public ?string $context;
    public array $hints = [];
    /** @var array<int, string>|array<class-string, array<int, string>> */
    public array $includes = [];
    public mixed $resource = null;

    public function __construct(mixed $subject, ?string $context = null, array $hints = [], array $includes = []);
    public function isResolved(): bool;
    public function setResource(mixed $resource): void;
    public function hasIncludes(): bool;
    public function includesFor(?object $forSubject = null): array;
    public function addIncludes(array|string $relations, ?string $forClass = null): void;
}
```

### Parameters & Properties

- **`$subject`** *(mixed)*: The instance you want to render (Eloquent model, DTO, etc.).
- **`$context`** *(?string)*: Short string describing usage target (e.g., `api.list`, `api.detail`, `web.card`, `admin.table`, `export.csv`).  
  Use dot‑notation for clarity: `<channel>.<view>.<audience?>` (e.g., `api.detail.private`).
- **`$hints`** *(array<string,mixed>)*: Fine‑grained knobs. Common keys:
  - Presentation: `fields`, `compact`, `depth`, `wrap`, `transformer`, `schema_version`
  - Policy/Security: `viewer_id|viewer`, `visibility`, `with_permissions`, `redact`
  - Format/Locale: `locale`, `timezone`, `currency`, `date_format`, `number_format`
  - Behavior/Performance: `forbid_db` (bool), `already_eager_loaded` (bool), `cacheable` (bool), `cache_ttl` (int)
  - Relations (legacy compatibility): `includes` (prefer `$includes` property now)
- **`$includes`** *(array)*: Declares relations you **expect** to be available (eager‑loaded). Accepted shapes:
  - Simple list (non‑polymorphic): `['category', 'tags']`
  - Polymorphic map: `[ Post::class => ['category'], Product::class => ['brand'] ]`
- **`$resource`** *(mixed)*: The result set by a listener (e.g., `JsonResource`, `Arrayable`, `array`, `string`, or `null`).

### Helper Methods

- `isResolved()` → Has any listener already set `$resource`?
- `setResource($resource)` → Assign the resolved representation (first writer wins).
- `hasIncludes()` → Did the caller declare any `includes`?
- `includesFor($forSubject)` → Normalize `includes` for a specific subject (handles polymorphic maps).
- `addIncludes($relations, $forClass = null)` → Mutably add includes programmatically.

---

## Context Strategy (Recommended)

Use dot‑notation with 2–3 parts:

1. **Channel**: `api`, `web`, `admin`, `export`, `cli`
2. **View**: `list`, `detail`, `card`, `row`, `embed`, `tile`
3. **Audience (optional)**: `public`, `private`, `owner`, `staff`

**Examples:** `api.list`, `api.detail`, `admin.table`, `web.card.public`, `export.csv`

Listeners can match exactly (`$event->context === 'api.list'`) or by prefix (`Str::startsWith($event->context, 'api')`).

---

## Includes: Deterministic Rendering with `whenLoaded()`

`includes` expresses what **should already be loaded** so resources can safely use `whenLoaded('relation')` with **no additional queries**. This avoids N+1 in lists and keeps listeners simple.

### Non‑Polymorphic Example
```php
$event = new ResourceResolveEvent(
    subject: $post,
    context: 'api.detail',
    includes: ['category', 'tags']  // expect these relations to be eager-loaded up-front
);
event($event);
```

### Polymorphic Example
```php
$event = new ResourceResolveEvent(
    subject: $slug->slugable,
    context: 'api.list',
    includes: [
        \App\Models\Post::class    => ['category'],
        \App\Models\Product::class => ['brand'],
    ]
);
event($event);
```

> **Tip**: In callers, eager‑load with `morphWith`/`loadMorph` to guarantee those relations are preloaded.

---

## Eager‑loading Patterns (Callers)

### A) Query‑time includes (preferred)
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
    ->get();

foreach ($slugs as $slug) {
    $event = new ResourceResolveEvent(
        subject: $slug->slugable,
        context: 'api.list',
        includes: [
            \App\Models\Post::class    => ['category'],
            \App\Models\Product::class => ['brand'],
        ],
        hints: ['forbid_db' => true]
    );
    event($event);
    $out[] = $event->resource;
}
```

### B) After `get()` (collection‑time includes)
```php
$slugs = Slug::with('slugable')->get();

$slugs->loadMorph('slugable', [
    \App\Models\Post::class    => ['category'],
    \App\Models\Product::class => ['brand'],
]);
```

---

## Example Resources

### `PostResource` respecting `whenLoaded()`
```php
use Illuminate\Http\Resources\Json\JsonResource;

/** @property \App\Models\Post $resource */
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
            'tags'     => $this->whenLoaded('tags', fn () => $this->tags->pluck('name')),
        ];
    }
}
```

### `ProductResource`
```php
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

---

## Example Listeners

### Listener for `Post`
```php
use JobMetric\PackageCore\Events\ResourceResolveEvent;
use App\Http\Resources\PostResource;
use App\Models\Post;

final class ResolvePostResource
{
    public function handle(ResourceResolveEvent $event): void
    {
        if ($event->isResolved() || ! $event->subject instanceof Post) {
            return;
        }

        // Optional: enforce "no extra queries" on list contexts
        $forbidDb = (bool) ($event->hints['forbid_db'] ?? false);
        if ($forbidDb) {
            // Do NOT lazy-load; rely on includes/eager-loaded relations
        }

        // Choose transformer by context (example)
        $resource = new PostResource($event->subject);
        $event->setResource($resource);
    }
}
```

### Listener for `Product`
```php
use JobMetric\PackageCore\Events\ResourceResolveEvent;
use App\Http\Resources\ProductResource;
use App\Models\Product;

final class ResolveProductResource
{
    public function handle(ResourceResolveEvent $event): void
    {
        if ($event->isResolved() || ! $event->subject instanceof Product) {
            return;
        }

        $event->setResource(new ProductResource($event->subject));
    }
}
```

> **Register both** in `EventServiceProvider::$listen` for `ResourceResolveEvent::class`.

---

## Using the Event in a Model Accessor

Sometimes you want a computed attribute that returns the resolved resource.

```php
class Slug extends Model
{
    protected array $__resolved = [];

    public function getGlobalResourceAttribute(): mixed
    {
        if (array_key_exists('global_resource', $this->__resolved)) {
            return $this->__resolved['global_resource'];
        }

        $subject = $this->slugable; // assume relation loaded by caller
        if (! $subject) {
            return $this->__resolved['global_resource'] = null;
        }

        $event = new \JobMetric\PackageCore\Events\ResourceResolveEvent(
            subject: $subject,
            context: 'api.list',
            includes: [
                \App\Models\Post::class    => ['category'],
                \App\Models\Product::class => ['brand'],
            ],
            hints: ['forbid_db' => true]
        );

        event($event);

        return $this->__resolved['global_resource'] = $event->resource;
    }
}
```

> **Memoization** prevents re‑dispatching the event multiple times for the same model instance in a single request.

---

## Advanced: Switching Transformers via Hints

You can override which resource class is used per call via `hints['transformer']`:

```php
// Caller:
$event = new ResourceResolveEvent(
    subject: $post,
    context: 'api.list',
    hints: ['transformer' => \App\Http\Resources\PostSummaryResource::class],
    includes: ['category']
);
event($event);

// Listener:
if ($event->subject instanceof \App\Models\Post) {
    $transformer = $event->hints['transformer'] ?? \App\Http\Resources\PostResource::class;
    $event->setResource(app($transformer, ['resource' => $event->subject]));
}
```

---

## Policy & Viewer‑aware Fields

```php
// Caller passes viewer info
$event = new ResourceResolveEvent(
    subject: $post,
    context: 'api.detail.private',
    hints: ['viewer_id' => auth()->id(), 'visibility' => 'owner']
);
event($event);

// Listener guards sensitive fields based on viewer/visibility
```

Inside your `JsonResource`, you can include permission meta or conditional sections.

---

## Performance Guidelines

- **Eager‑load** declared `includes` in callers (query‑time or `loadMorph`).  
- **Avoid lazy‑loading** in listeners for lists: respect `forbid_db`.  
- **Synchronous listeners only** (`ShouldQueue` is not recommended) to keep the attribute access inline.  
- **Single‑writer**: the first capable listener sets the resource; others bail (`$event->isResolved()`).  
- In Dev, enable `Model::preventLazyLoading()` and watch DB query logs to catch regressions.

---

## Testing Patterns

### A) Listener selection and output
```php
public function test_post_listener_sets_resource()
{
    $post = Post::factory()->make();

    $event = new ResourceResolveEvent($post, 'api.detail', includes: ['category']);
    event($event);

    $this->assertNotNull($event->resource);
    $this->assertInstanceOf(\Illuminate\Http\Resources\Json\JsonResource::class, $event->resource);
}
```

### B) No extra queries when `forbid_db` is set
```php
DB::enableQueryLog();

$event = new ResourceResolveEvent($post, 'api.list', hints: ['forbid_db' => true], includes: ['category']);
event($event);

$queries = collect(DB::getQueryLog());
$this->assertTrue($queries->isEmpty(), 'Listener executed unexpected queries');
```

### C) Event registration sanity
```php
Event::fake();
event(new ResourceResolveEvent(Post::factory()->make()));
Event::assertDispatched(ResourceResolveEvent::class);
```

---

## Common Pitfalls & Anti‑patterns

- **Queuing listeners**: The accessor returns before your resource is ready. Keep them sync.
- **Multiple writers**: Two listeners set `$event->resource`; the last wins → nondeterminism. Always bail if `isResolved()`.
- **Lazy‑loading in lists**: Triggers N+1. Use caller‑side `includes` and `forbid_db` to enforce a no‑DB contract.
- **Ambiguous contexts**: Use clear dot‑notation so listeners don’t guess (e.g., `api.list` vs `api.detail`).

---

## Migration Guide (from a custom `UrlableResourceEvent`)

- Replace your event with `ResourceResolveEvent` in `JobMetric\PackageCore\Events`.
- Move any `hints['includes']` usage to the dedicated `$includes` property where possible.
- Adjust listeners to respect `isResolved()` and optional `forbid_db`.
- Keep the same resources; only the entry point changes.

---

## FAQ

**Q: Can a listener return a plain array instead of a JsonResource?**  
A: Yes. `$event->resource` can be an array, `Arrayable`, `JsonSerializable`, or a `JsonResource`.

**Q: What if no listener handles the subject?**  
A: `$event->resource` remains `null`. You can add a fallback listener or a contract on subjects (`toResolvedResource()`) as a safety net.

**Q: How to handle multi‑tenancy or environment‑specific output?**  
A: Use `context` (e.g., `admin.detail`) and `hints` (e.g., `tenant_id`, `environment`) to guide what fields/relations appear.

**Q: Should I cache the output?**  
A: For hot paths, you can cache per subject/version/context. Provide `cacheable`/`cache_ttl` hints and implement caching inside listeners.

---

## License
This documentation and example code are provided under the same license as your package or project. Adapt freely within your codebase.
