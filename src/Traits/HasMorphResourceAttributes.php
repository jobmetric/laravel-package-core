<?php

namespace JobMetric\PackageCore\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;
use JobMetric\PackageCore\Events\ResourceResolveEvent;
use ReflectionMethod;
use Throwable;

/**
 * Trait HasMorphResourceAttributes
 *
 * Provides dynamic "<relation>_resource" virtual attributes for each MorphTo relation on a model.
 * When accessed, it dispatches ResourceResolveEvent with configurable context/hints/includes.
 * - No lazy-loading inside the trait: if relation isn't loaded, returns null (prevents N+1).
 * - Memoizes results per model instance during the request.
 * - Autodiscovery of MorphTo relations is ON by default; can be disabled or narrowed via config.
 *
 * Model-level optional configuration:
 *   // Explicit relations to expose (unioned with autodiscovered ones if enabled)
 *   protected array $resourceMorphRelations = ['slugable'];
 *
 *   // Turn autodiscovery on/off (default: true if not defined)
 *   protected bool $resourceMorphAutoDiscover = true;
 *
 *   // Exclude some relations from autodiscovery (by method name)
 *   protected array $resourceMorphDiscoveryExcept = ['internalable'];
 *
 *   // Per-relation includes (simple list or polymorphic map)
 *   protected array $resourceMorphIncludes = [
 *       'slugable' => [
 *           \App\Models\Post::class    => ['category'],
 *           \App\Models\Product::class => ['brand'],
 *       ],
 *   ];
 *
 *   // Per-relation context
 *   protected array $resourceMorphContexts = ['slugable' => 'api.list'];
 *
 *   // Per-relation hints
 *   protected array $resourceMorphHintsByRelation = ['slugable' => ['forbid_db' => true]];
 *
 *   // Defaults
 *   protected ?string $resourceMorphDefaultContext = 'api.list';
 *   protected array $resourceMorphDefaultHints = ['forbid_db' => true];
 *
 * Usage:
 *   $model->slugable_resource;              // dynamic attribute
 *   $model->resolveMorphResource('slugable', context: 'api.detail'); // explicit call
 *
 * @property-read string $resource_morph_default_context Default message if no context is set.
 */
trait HasMorphResourceAttributes
{
    /**
     * Per-request memoization for resolved resources keyed by relation name.
     *
     * @var array<string, mixed>
     */
    protected array $morphResourceCache = [];

    /**
     * Static cache: discovered MorphTo relation names per model class (unfiltered).
     * Exclusions are applied per instance at runtime, not stored here.
     *
     * @var array<class-string, array<int, string>>
     */
    protected static array $morphDiscoveryCache = [];

    /**
     * Intercepts "<relation>_resource" attributes and resolves them for MorphTo relations.
     * Supports snake_case and camelCase tokens (e.g., "az_bs_resource" -> "azBs()").
     *
     * @param string $key
     *
     * @return mixed
     */
    public function getAttribute($key): mixed
    {
        if (is_string($key) && str_ends_with($key, '_resource')) {
            $token = Str::beforeLast($key, '_resource');

            // Map token to an actual MorphTo relation method
            $relation = $this->matchMorphRelationToken($token);

            if ($relation !== null) {
                return $this->resolveMorphResource($relation);
            }
        }

        return parent::getAttribute($key);
    }

    /**
     * Default message for unresolved resources if no default context is set.
     *
     * @return string
     */
    public function getResourceMorphDefaultContextAttribute(): string
    {
        return 'Resource is not resolved. Please read: '
            .'https://github.com/jobmetric/laravel-package-core/blob/master/docs/has-morph-resource-attributes.md';
    }

    /**
     * Resolves a resource for a given MorphTo relation via ResourceResolveEvent.
     * Never lazy-loads the relation; returns null if it's not loaded.
     *
     * @param string $relation
     * @param string|null $context
     * @param array<int, string>|array<class-string, array<int, string>> $includes
     * @param array<string, mixed> $hints
     *
     * @return mixed
     */
    public function resolveMorphResource(string $relation, ?string $context = null, array $includes = [], array $hints = []): mixed
    {
        $cacheKey = $relation;

        if (array_key_exists($cacheKey, $this->morphResourceCache)) {
            return $this->morphResourceCache[$cacheKey];
        }

        // N+1-safe: only proceed if relation is already loaded
        $subject = $this->relationLoaded($relation) ? $this->getRelation($relation) : null;
        if (!$subject) {
            return $this->morphResourceCache[$cacheKey] = null;
        }

        $mergedIncludes = $this->resolveIncludesFor($relation, $includes);
        $mergedHints = $this->resolveHintsFor($relation, $hints);
        $finalContext = $this->resolveContextFor($relation, $context);

        $event = new ResourceResolveEvent(
            subject: $subject,
            context: $finalContext,
            hints: $mergedHints,
            includes: $mergedIncludes
        );

        event($event);

        return $this->morphResourceCache[$cacheKey] = $event->resource;
    }

    /**
     * Attempts to find a MorphTo relation name that matches the given token.
     * Priority:
     *  1) exact method name
     *  2) camel(token) or lcfirst(studly(token)) equals relation name
     *  3) token or snake(token) equals snake(relation)
     *  4) relaxed intersection over normalized variants
     *
     * @param string $token
     *
     * @return string|null
     */
    protected function matchMorphRelationToken(string $token): ?string
    {
        $declared = $this->declaredMorphRelations();

        // Exact method name
        foreach ($declared as $rel) {
            if ($token === $rel && $this->isMorphRelation($rel)) {
                return $rel;
            }
        }

        // Variants
        $tokenCamel = Str::camel($token);           // az_bs -> azBs
        $tokenSnake = Str::snake($token);           // azBs -> az_bs (if given camel)
        $tokenStudlyL = lcfirst(Str::studly($token)); // az_bs -> azBs

        foreach ($declared as $rel) {
            if (!$this->isMorphRelation($rel)) {
                continue;
            }

            if ($tokenCamel === $rel || $tokenStudlyL === $rel) {
                return $rel;
            }

            $relSnake = Str::snake($rel);
            $relCamel = Str::camel($rel);
            $relStudlyL = lcfirst(Str::studly($rel));

            if ($tokenSnake === $relSnake || $token === $relSnake) {
                return $rel;
            }

            // Relaxed fallback
            $tokenSet = [$token, $tokenCamel, $tokenSnake, $tokenStudlyL];
            $relSet = [$rel, $relSnake, $relCamel, $relStudlyL];

            if (count(array_intersect($tokenSet, $relSet)) > 0) {
                return $rel;
            }
        }

        return null;
    }

    /**
     * Checks whether the given relation name exists on this model and is a MorphTo.
     *
     * @param string $relation
     * @return bool
     */
    protected function isMorphRelation(string $relation): bool
    {
        $relations = $this->declaredMorphRelations();
        if (!in_array($relation, $relations, true)) {
            return false;
        }

        try {
            if (!method_exists($this, $relation)) {
                return false;
            }

            $rel = $this->{$relation}();

            return $rel instanceof MorphTo;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Returns the union of explicitly declared morph relations and autodiscovered ones (by default).
     * Autodiscovery results are class-cached, but per-instance excludes are applied at runtime.
     *
     * @return array<int, string>
     */
    protected function declaredMorphRelations(): array
    {
        $class = static::class;

        // Explicit
        $declared = property_exists($this, 'resourceMorphRelations') && is_array($this->resourceMorphRelations)
            ? array_values(array_unique($this->resourceMorphRelations))
            : [];

        // Autodiscovery: default ON if property not defined
        $auto = !property_exists($this, 'resourceMorphAutoDiscover') || (bool)$this->resourceMorphAutoDiscover;

        if ($auto) {
            if (!isset(self::$morphDiscoveryCache[$class])) {
                self::$morphDiscoveryCache[$class] = $this->discoverMorphRelations();
            }

            // Apply per-instance excludes (not cached globally)
            $except = property_exists($this, 'resourceMorphDiscoveryExcept') && is_array($this->resourceMorphDiscoveryExcept)
                ? $this->resourceMorphDiscoveryExcept
                : [];

            $discovered = self::$morphDiscoveryCache[$class];
            if ($except !== []) {
                $discovered = array_values(array_diff($discovered, $except));
            }

            $declared = array_values(array_unique(array_merge($declared, $discovered)));
        }

        return $declared;
    }

    /**
     * Discovers MorphTo relations by scanning public, parameterless methods returning MorphTo.
     * Called at most once per model class (cached).
     *
     * @return array<int, string>
     */
    protected function discoverMorphRelations(): array
    {
        $out = [];
        $class = static::class;
        $baseMethods = get_class_methods(Model::class);
        $methods = array_diff(get_class_methods($class), $baseMethods);

        foreach ($methods as $name) {
            try {
                $ref = new ReflectionMethod($class, $name);

                if (!$ref->isPublic() || $ref->isStatic() || $ref->getNumberOfParameters() > 0) {
                    continue;
                }

                // Calling relation methods creates Relation objects, no DB hit.
                $ret = $this->{$name}();

                if ($ret instanceof MorphTo) {
                    $out[] = $name;
                }
            } catch (Throwable) {
                continue; // Ignore non-relation public methods that error when called
            }
        }

        return $out;
    }

    /**
     * Resolves effective includes for a relation, merging config with call-time overrides.
     * Shape (list vs map) is preserved.
     *
     * @param string $relation
     * @param array<int, string>|array<class-string, array<int, string>> $overrides
     * @return array<int, string>|array<class-string, array<int, string>>
     */
    protected function resolveIncludesFor(string $relation, array $overrides = []): array
    {
        /** @var array<string, mixed> $cfg */
        $cfg = property_exists($this, 'resourceMorphIncludes') && is_array($this->resourceMorphIncludes)
            ? $this->resourceMorphIncludes
            : [];

        $base = $cfg[$relation] ?? [];

        // Preserve shape
        if ($this->isAssoc($base) || $this->isAssoc($overrides)) {
            return $this->mergePolyMap(
                is_array($base) ? $base : [],
                is_array($overrides) ? $overrides : []
            );
        }

        return array_values(array_unique(array_merge(
            is_array($base) ? $base : [],
            is_array($overrides) ? $overrides : []
        )));
    }

    /**
     * Resolves effective hints for a relation: defaults → per-relation → call-time.
     *
     * @param string $relation
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    protected function resolveHintsFor(string $relation, array $overrides = []): array
    {
        $defaults = property_exists($this, 'resourceMorphDefaultHints') && is_array($this->resourceMorphDefaultHints)
            ? $this->resourceMorphDefaultHints
            : [];

        $perRelation = property_exists($this, 'resourceMorphHintsByRelation') && is_array($this->resourceMorphHintsByRelation)
            ? ($this->resourceMorphHintsByRelation[$relation] ?? [])
            : [];

        return array_replace_recursive($defaults, $perRelation, $overrides);
    }

    /**
     * Resolves effective context for a relation: call-time override → per-relation → default.
     *
     * @param string $relation
     * @param string|null $override
     * @return string|null
     */
    protected function resolveContextFor(string $relation, ?string $override = null): ?string
    {
        if ($override !== null) {
            return $override;
        }

        $perRelation = property_exists($this, 'resourceMorphContexts') && is_array($this->resourceMorphContexts)
            ? ($this->resourceMorphContexts[$relation] ?? null)
            : null;

        if ($perRelation !== null) {
            return $perRelation;
        }

        return property_exists($this, 'resourceMorphDefaultContext') ? $this->resourceMorphDefaultContext : null;
    }

    /**
     * Merges two polymorphic include maps of shape [class-string => string[]].
     * Values are unique; keys are ksorted for determinism.
     *
     * @param array<class-string, array<int, string>> $a
     * @param array<class-string, array<int, string>> $b
     * @return array<class-string, array<int, string>>
     */
    protected function mergePolyMap(array $a, array $b): array
    {
        $classes = array_unique(array_merge(array_keys($a), array_keys($b)));
        $out = [];

        foreach ($classes as $cls) {
            $list = array_merge($a[$cls] ?? [], $b[$cls] ?? []);
            $out[$cls] = array_values(array_unique($list));
        }

        ksort($out);

        return $out;
    }

    /**
     * Determines if an array is associative (non-sequential integer keys).
     *
     * @param array $arr
     * @return bool
     */
    protected function isAssoc(array $arr): bool
    {
        if ($arr === []) {
            return false;
        }

        return array_keys($arr) !== range(0, count($arr) - 1);
    }
}
