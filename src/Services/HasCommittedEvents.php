<?php

namespace JobMetric\PackageCore\Services;

use Illuminate\Database\Eloquent\Model;
use JobMetric\EventSystem\Contracts\DomainEvent;
use JobMetric\EventSystem\Support\CommittedEventDispatcher;
use JobMetric\EventSystem\Support\OutcomeState;
use LogicException;

/**
 * Map CRUD business outcomes to minimal event classes without changing legacy events.
 */
trait HasCommittedEvents
{
    /** @var array<string, class-string<DomainEvent>> */
    protected static array $committedEventClasses = [];

    /** @var list<string> */
    protected array $committedEventRelations = [];

    /**
     * Capture comparison state only when this service has opted into safe update outcomes.
     *
     * @param Model $model
     *
     * @return array<string, mixed>|null
     */
    protected function committedEventState(Model $model): ?array
    {
        return isset(static::$committedEventClasses['update'])
            ? OutcomeState::capture($model, $this->committedEventRelations)
            : null;
    }

    /**
     * Schedule an explicitly configured outcome with only its persisted identifier.
     *
     * @param string $operation
     * @param Model $model
     * @param bool $changed
     *
     * @return void
     */
    protected function fireCommittedEvent(string $operation, Model $model, bool $changed = true): void
    {
        $class = static::$committedEventClasses[$operation] ?? null;
        if (! $class || ! $changed) {
            return;
        }
        if (! is_subclass_of($class, DomainEvent::class)) {
            throw new LogicException('Committed event mappings must implement DomainEvent.');
        }

        app(CommittedEventDispatcher::class)->dispatch(new $class($model->getKey()), $model->getConnectionName());
    }
}
