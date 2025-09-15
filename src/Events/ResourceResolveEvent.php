<?php

namespace JobMetric\PackageCore\Events;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

/**
 * Class ResourceResolveEvent
 *
 * Provides a global, package-agnostic event to resolve a "resource" representation for any subject.
 * Listeners inspect the subject, optional context, hints, and desired includes, then assign the resulting resource.
 */
class ResourceResolveEvent
{
    /**
     * Holds the target instance to resolve a resource for (e.g., Eloquent model, DTO).
     * Role: The primary input that resolvers will inspect to build a representation.
     *
     * @var mixed
     */
    public mixed $subject;

    /**
     * Optional resolution context (e.g., "api", "admin", "web", "list", "detail") to guide listeners.
     * Role: Communicates where/how the representation is intended to be used.
     *
     * @var string|null
     */
    public ?string $context;

    /**
     * Optional free-form hints to influence resolution (e.g., "fields", "compact", "locale", "forbid_db").
     * Role: Fine-grained switches that avoid hardcoded logic in listeners.
     *
     * @var array<string, mixed>
     */
    public array $hints = [];

    /**
     * Desired relations to be considered as eager-loaded for the subject.
     * Role: Declares which relations the caller expects to be available so listeners can safely use whenLoaded().
     *
     * Supported shapes:
     * - Non-polymorphic list: ['category', 'tags']
     * - Polymorphic map: [ \App\Models\Post::class => ['category'], \App\Models\Product::class => ['brand'] ]
     *
     * Note: This property complements (and supersedes) using $hints['includes'].
     *
     * @var array<int, string>|array<class-string, array<int, string>>
     */
    public array $includes = [];

    /**
     * The resolved representation assigned by exactly one listener (single-writer convention).
     * Role: Output container to be set by the first capable resolver.
     *
     * @var JsonResource|Arrayable|JsonSerializable|array|string|null
     */
    public mixed $resource = null;

    /**
     * Create a new event instance.
     *
     * Explains: Initializes the event with a subject to resolve and optional context/hints/includes that guide listeners.
     *
     * @param mixed $subject The target instance to resolve a resource for.
     * @param string|null $context Optional context such as "api", "list", or "admin".
     * @param array<string, mixed> $hints Optional hints to influence resolution (e.g., fields, compact, forbid_db).
     * @param array<int, string>|array<class-string, array<int, string>> $includes Desired relations (list or polymorphic map).
     *
     * @return void
     */
    public function __construct(mixed $subject, ?string $context = null, array $hints = [], array $includes = [])
    {
        $this->subject = $subject;
        $this->context = $context;
        $this->hints = $hints;
        $this->includes = $includes;

        // Backward-compat: allow hints['includes'] to populate includes if not explicitly provided.
        if (empty($this->includes) && isset($this->hints['includes']) && is_array($this->hints['includes'])) {
            $this->includes = $this->hints['includes'];
        }
    }

    /**
     * Indicates whether the resource has been resolved by a listener.
     *
     * @return bool True when the resource has been assigned; otherwise, false.
     */
    public function isResolved(): bool
    {
        return $this->resource !== null;
    }

    /**
     * Sets the resolved resource value.
     *
     * Explains: Listeners should call this once they build the representation. By convention,
     * only the first listener should assign; subsequent listeners must bail if already resolved.
     *
     * @param JsonResource|Arrayable|JsonSerializable|array|string|null $resource The resolved representation.
     *
     * @return void
     */
    public function setResource(mixed $resource): void
    {
        $this->resource = $resource;
    }

    /**
     * Checks whether any includes have been declared.
     *
     * Explains: Helps listeners quickly decide if relation-dependent rendering paths are allowed.
     *
     * @return bool True if includes are defined; otherwise, false.
     */
    public function hasIncludes(): bool
    {
        return !empty($this->includes);
    }

    /**
     * Returns the list of includes applicable to the given subject (or this event's subject if none provided).
     *
     * Explains: Normalizes both shapes of includes (list vs polymorphic map) so listeners can use them directly.
     *
     * @param object|null $forSubject Optional subject to resolve includes for (useful in polymorphic dispatch).
     *
     * @return array<int, string> A flat list of relation names relevant to the subject.
     */
    public function includesFor(?object $forSubject = null): array
    {
        $target = $forSubject ?? (is_object($this->subject) ? $this->subject : null);
        if (!$this->hasIncludes()) {
            return [];
        }

        // If provided as a simple list, return it for any subject.
        if ($this->isList($this->includes)) {
            return $this->includes;
        }

        // Polymorphic: resolve by class.
        if ($target) {
            $class = get_class($target);
            $map = $this->includes;

            return isset($map[$class]) && is_array($map[$class]) ? $map[$class] : [];
        }

        return [];
    }

    /**
     * Adds one or more includes to this event.
     *
     * Explains: Allows callers or early listeners to augment includes before a resolver consumes them.
     *
     * @param array<int, string>|string $relations One relation or a list of relations.
     * @param class-string|null $forClass Optional class when building a polymorphic map.
     *
     * @return void
     */
    public function addIncludes(array|string $relations, ?string $forClass = null): void
    {
        $rels = is_array($relations) ? $relations : [$relations];

        // If a target class is given, maintain/convert to polymorphic map.
        if ($forClass) {
            if ($this->isList($this->includes)) {
                // Convert existing list into a map keyed by current subject class if possible.
                $existing = $this->includes;
                $this->includes = [];
                if (is_object($this->subject)) {
                    $this->includes[get_class($this->subject)] = $existing;
                }
            }

            $current = $this->includes[$forClass] ?? [];
            $this->includes[$forClass] = array_values(array_unique(array_merge($current, $rels)));

            return;
        }

        // No class provided: keep/merge as a simple list.
        if ($this->isList($this->includes)) {
            $this->includes = array_values(array_unique(array_merge($this->includes, $rels)));
            return;
        }

        // Already a map: attach to the event subject's class if possible; otherwise, ignore silently.
        if (is_object($this->subject)) {
            $class = get_class($this->subject);
            $current = $this->includes[$class] ?? [];
            $this->includes[$class] = array_values(array_unique(array_merge($current, $rels)));
        }
    }

    /**
     * Determines if the given array is a sequential list (non-associative).
     *
     * @param array<mixed> $arr The array to test.
     *
     * @return bool True if the array is a list; otherwise, false.
     */
    private function isList(array $arr): bool
    {
        $i = 0;
        foreach (array_keys($arr) as $key) {
            if ($key !== $i) {
                return false;
            }
            $i++;
        }

        return true;
    }
}
