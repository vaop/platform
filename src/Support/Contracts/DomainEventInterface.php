<?php

declare(strict_types=1);

namespace Support\Contracts;

use DateTimeImmutable;

/**
 * Interface for domain events.
 *
 * Domain events represent something significant that happened in the domain.
 * They are immutable records of past occurrences that can be used for
 * cross-domain communication, audit logging, and event sourcing.
 */
interface DomainEventInterface
{
    /**
     * Get the unique identifier for this event instance.
     */
    public function eventId(): string;

    /**
     * Get when this event occurred.
     */
    public function occurredAt(): DateTimeImmutable;

    /**
     * Get the aggregate/entity ID this event relates to.
     */
    public function aggregateId(): string|int;

    /**
     * Get the event name for serialization/logging.
     */
    public static function eventName(): string;

    /**
     * Serialize the event to an array for storage/transport.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array;

    /**
     * Reconstruct the event from array data.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): static;
}
