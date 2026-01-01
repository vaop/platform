<?php

declare(strict_types=1);

namespace Support\Events;

use DateTimeImmutable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Support\Contracts\DomainEventInterface;

/**
 * Abstract base class for domain events.
 *
 * Domain events represent significant occurrences within the domain.
 * They are immutable and carry all the information needed to understand
 * what happened.
 *
 * Usage:
 *   final class UserRegistered extends DomainEvent
 *   {
 *       public function __construct(
 *           private readonly int $userId,
 *           private readonly string $email,
 *       ) {
 *           parent::__construct($userId);
 *       }
 *
 *       public static function eventName(): string
 *       {
 *           return 'user.registered';
 *       }
 *
 *       public function toArray(): array
 *       {
 *           return [
 *               ...parent::toArray(),
 *               'user_id' => $this->userId,
 *               'email' => $this->email,
 *           ];
 *       }
 *   }
 */
abstract class DomainEvent implements DomainEventInterface
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    private readonly string $eventId;

    private readonly DateTimeImmutable $occurredAt;

    /**
     * Create a new domain event.
     *
     * @param  string|int  $aggregateId  The ID of the aggregate/entity this event relates to
     * @param  string|null  $eventId  Optional event ID (auto-generated if not provided)
     * @param  DateTimeImmutable|null  $occurredAt  Optional occurrence time (defaults to now)
     */
    public function __construct(
        private readonly string|int $aggregateId,
        ?string $eventId = null,
        ?DateTimeImmutable $occurredAt = null,
    ) {
        $this->eventId = $eventId ?? (string) Str::uuid();
        $this->occurredAt = $occurredAt ?? new DateTimeImmutable;
    }

    /**
     * Get the unique identifier for this event instance.
     */
    public function eventId(): string
    {
        return $this->eventId;
    }

    /**
     * Get when this event occurred.
     */
    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }

    /**
     * Get the aggregate/entity ID this event relates to.
     */
    public function aggregateId(): string|int
    {
        return $this->aggregateId;
    }

    /**
     * Get the event name for serialization/logging.
     *
     * Subclasses should override this to provide a meaningful event name.
     * Convention: domain.action (e.g., 'user.registered', 'pirep.accepted')
     */
    abstract public static function eventName(): string;

    /**
     * Serialize the event to an array for storage/transport.
     *
     * Subclasses should call parent::toArray() and merge their own data.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'event_id' => $this->eventId,
            'event_name' => static::eventName(),
            'aggregate_id' => $this->aggregateId,
            'occurred_at' => $this->occurredAt->format('Y-m-d\TH:i:s.uP'),
        ];
    }

    /**
     * Reconstruct the event from array data.
     *
     * Subclasses must implement this method to restore their specific properties.
     *
     * @param  array<string, mixed>  $data
     */
    abstract public static function fromArray(array $data): static;

    /**
     * Helper to parse the occurred_at timestamp from array data.
     */
    protected static function parseOccurredAt(array $data): DateTimeImmutable
    {
        if (isset($data['occurred_at'])) {
            return new DateTimeImmutable($data['occurred_at']);
        }

        return new DateTimeImmutable;
    }
}
