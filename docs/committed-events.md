# Committed business outcomes

Services opt in with `protected static array $committedEventClasses`, keyed by store, update, destroy, restore and forceDelete. Event constructors receive only the model key. Configure `committedEventRelations` for business relationships. The optional event-system dependency is needed only for opted-in services. Legacy events keep their current dispatch path.

Schedule from the owning operation after successful persistence. Delivery waits for the owning connection outer commit and is discarded on rollback. No-op operations emit nothing. Native Laravel listeners are isolated individually; failures are logged with event key and exception class only. No models, raw inputs, credentials or translated content are included. This is synchronous delivery without durable outbox guarantees.
