<?php

namespace JobMetric\PackageCore\Tests\Feature;

use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Database\Connectors\ConnectionFactory;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\DatabaseTransactionsManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Events\Dispatcher;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Facade;
use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use InvalidArgumentException;
use JobMetric\EventSystem\Contracts\DomainEvent;
use JobMetric\EventSystem\Support\DomainEventDefinition;
use JobMetric\PackageCore\Services\AbstractCrudService;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use RuntimeException;

class CommittedCrudEventsTest extends TestCase
{
    private Container $container;
    private DatabaseManager $database;
    private Dispatcher $events;
    private FixtureCrudService $service;
    private array $outcomes = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->container = new Container;
        Container::setInstance($this->container);
        $this->container->instance('config', new Repository(['database' => [
            'default' => 'testing', 'connections' => ['testing' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']],
        ]]));
        $this->events = new Dispatcher($this->container);
        $this->container->instance('events', $this->events);
        $this->container->instance(\Illuminate\Contracts\Events\Dispatcher::class, $this->events);
        $this->container->instance(LoggerInterface::class, new NullLogger);
        $this->container->instance('db.transactions', new DatabaseTransactionsManager);
        $this->database = new DatabaseManager($this->container, new ConnectionFactory($this->container));
        $this->container->instance('db', $this->database);
        $this->container->instance(DatabaseManager::class, $this->database);
        $this->container->instance('translator', new Translator(new ArrayLoader, 'en'));
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication($this->container);
        Model::setConnectionResolver($this->database);
        Model::setEventDispatcher($this->events);
        $this->database->connection()->statement('CREATE TABLE automation_records (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT NOT NULL, status INTEGER DEFAULT 1, created_at DATETIME, updated_at DATETIME, deleted_at DATETIME)');
        $this->outcomes = [];
        $this->events->listen(DomainEvent::class, function (DomainEvent $event): void {
            $this->outcomes[] = [$event::key(), get_object_vars($event)];
        });
        $this->service = new FixtureCrudService;
    }

    protected function tearDown(): void
    {
        $this->database->disconnect();
        FixtureCrudRecord::clearBootedModels();
        Model::unsetConnectionResolver();
        Model::unsetEventDispatcher();
        Facade::clearResolvedInstances();
        Container::setInstance(null);
        parent::tearDown();
    }

    public function test_store_waits_for_outer_commit_without_changing_legacy_signature_or_timing(): void
    {
        $legacy = [];
        $this->events->listen(LegacyCrudEvent::class, function (LegacyCrudEvent $event) use (&$legacy): void {
            $legacy[] = [$event->model->name, $event->data['name'], $this->database->transactionLevel()];
        });
        $this->database->beginTransaction();
        $response = $this->service->store(['name' => 'first']);
        self::assertTrue($response->ok);
        self::assertSame([['first', 'first', 2]], $legacy);
        self::assertSame([], $this->outcomes);
        $this->database->commit();
        self::assertSame([['fixture.stored', ['id' => 1]]], $this->outcomes);
    }

    public function test_update_skips_noops_and_all_delete_restore_outcomes_are_emitted_once(): void
    {
        $this->service->store(['name' => 'first']);
        $this->service->update(1, ['name' => 'first']);
        self::assertCount(1, $this->outcomes);
        $this->service->update(1, ['name' => 'changed']);
        $this->service->destroy(1);
        $this->service->restore(1);
        $this->service->destroy(1);
        $this->service->forceDelete(1);
        self::assertSame(['fixture.stored', 'fixture.updated', 'fixture.deleted', 'fixture.restored', 'fixture.deleted', 'fixture.force_deleted'], array_column($this->outcomes, 0));
    }

    public function test_rollback_validation_and_legacy_failure_do_not_emit_safe_outcomes(): void
    {
        $this->database->beginTransaction();
        $this->service->store(['name' => 'rolled back']);
        $this->database->rollBack();
        self::assertSame([], $this->outcomes);
        try {
            $this->service->store(['name' => '']);
            self::fail('Expected invalid input');
        } catch (InvalidArgumentException) {
            self::assertSame(0, FixtureCrudRecord::count());
        }
        $this->events->listen(LegacyCrudEvent::class, fn () => throw new RuntimeException('veto'));
        try {
            $this->service->store(['name' => 'vetoed']);
            self::fail('Expected legacy failure');
        } catch (RuntimeException) {
            self::assertSame(0, FixtureCrudRecord::count());
        }
        self::assertSame([], $this->outcomes);
    }

    public function test_safe_listener_failure_keeps_successful_response_and_later_listeners(): void
    {
        $this->events->listen(FixtureStored::class, fn () => throw new RuntimeException('sensitive detail'));
        self::assertTrue($this->service->store(['name' => 'persisted'])->ok);
        self::assertSame('persisted', FixtureCrudRecord::firstOrFail()->name);
        self::assertSame([['fixture.stored', ['id' => 1]]], $this->outcomes);
    }
}

class FixtureCrudRecord extends Model
{
    use SoftDeletes;
    protected $table = 'automation_records';
    protected $fillable = ['name', 'status'];
}

class FixtureCrudService extends AbstractCrudService
{
    protected static string $modelClass = FixtureCrudRecord::class;
    protected static string $resourceClass = JsonResource::class;
    protected bool $softDelete = true;
    protected static ?string $storeEventClass = LegacyCrudEvent::class;
    protected static array $committedEventClasses = [
        'store' => FixtureStored::class, 'update' => FixtureUpdated::class,
        'destroy' => FixtureDeleted::class, 'restore' => FixtureRestored::class,
        'forceDelete' => FixtureForceDeleted::class,
    ];

    protected function changeFieldStore(array &$data): void
    {
        if (($data['name'] ?? '') === '') throw new InvalidArgumentException('name required');
    }
}

class LegacyCrudEvent
{
    public function __construct(public Model $model, public array $data) {}
}

readonly class FixtureStored implements DomainEvent
{
    public function __construct(public int|string $id) {}
    public static function key(): string { return 'fixture.stored'; }
    public static function definition(): DomainEventDefinition { return new DomainEventDefinition(static::key(), 'fixture', 'fixture'); }
}
readonly class FixtureUpdated extends FixtureStored { public static function key(): string { return 'fixture.updated'; } }
readonly class FixtureDeleted extends FixtureStored { public static function key(): string { return 'fixture.deleted'; } }
readonly class FixtureRestored extends FixtureStored { public static function key(): string { return 'fixture.restored'; } }
readonly class FixtureForceDeleted extends FixtureStored { public static function key(): string { return 'fixture.force_deleted'; } }
