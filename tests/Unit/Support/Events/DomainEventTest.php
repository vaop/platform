<?php

declare(strict_types=1);

namespace Tests\Unit\Support\Events;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use Support\Contracts\DomainEventInterface;
use Support\Events\DomainEvent;
use Tests\TestCase;

/**
 * Test implementation of DomainEvent for testing purposes.
 */
final class TestUserRegistered extends DomainEvent
{
    public function __construct(
        private readonly int $userId,
        private readonly string $email,
        ?string $eventId = null,
        ?DateTimeImmutable $occurredAt = null,
    ) {
        parent::__construct($userId, $eventId, $occurredAt);
    }

    public static function eventName(): string
    {
        return 'user.registered';
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function toArray(): array
    {
        return [
            ...parent::toArray(),
            'user_id' => $this->userId,
            'email' => $this->email,
        ];
    }

    public static function fromArray(array $data): static
    {
        return new self(
            userId: $data['user_id'],
            email: $data['email'],
            eventId: $data['event_id'] ?? null,
            occurredAt: self::parseOccurredAt($data),
        );
    }
}

class DomainEventTest extends TestCase
{
    #[Test]
    public function it_implements_domain_event_interface(): void
    {
        $event = new TestUserRegistered(1, 'test@example.com');

        $this->assertInstanceOf(DomainEventInterface::class, $event);
    }

    #[Test]
    public function it_generates_unique_event_id(): void
    {
        $event1 = new TestUserRegistered(1, 'test@example.com');
        $event2 = new TestUserRegistered(1, 'test@example.com');

        $this->assertNotEmpty($event1->eventId());
        $this->assertNotEquals($event1->eventId(), $event2->eventId());
    }

    #[Test]
    public function it_allows_custom_event_id(): void
    {
        $event = new TestUserRegistered(1, 'test@example.com', 'custom-id');

        $this->assertSame('custom-id', $event->eventId());
    }

    #[Test]
    public function it_records_occurred_at_timestamp(): void
    {
        $before = new DateTimeImmutable;
        $event = new TestUserRegistered(1, 'test@example.com');
        $after = new DateTimeImmutable;

        $this->assertInstanceOf(DateTimeImmutable::class, $event->occurredAt());
        $this->assertGreaterThanOrEqual($before, $event->occurredAt());
        $this->assertLessThanOrEqual($after, $event->occurredAt());
    }

    #[Test]
    public function it_allows_custom_occurred_at(): void
    {
        $customTime = new DateTimeImmutable('2025-01-01 12:00:00');
        $event = new TestUserRegistered(1, 'test@example.com', null, $customTime);

        $this->assertEquals($customTime, $event->occurredAt());
    }

    #[Test]
    public function it_returns_aggregate_id(): void
    {
        $event = new TestUserRegistered(42, 'test@example.com');

        $this->assertSame(42, $event->aggregateId());
    }

    #[Test]
    public function it_returns_event_name(): void
    {
        $this->assertSame('user.registered', TestUserRegistered::eventName());
    }

    #[Test]
    public function it_serializes_to_array(): void
    {
        $event = new TestUserRegistered(1, 'test@example.com');

        $array = $event->toArray();

        $this->assertArrayHasKey('event_id', $array);
        $this->assertArrayHasKey('event_name', $array);
        $this->assertArrayHasKey('aggregate_id', $array);
        $this->assertArrayHasKey('occurred_at', $array);
        $this->assertArrayHasKey('user_id', $array);
        $this->assertArrayHasKey('email', $array);

        $this->assertSame('user.registered', $array['event_name']);
        $this->assertSame(1, $array['aggregate_id']);
        $this->assertSame(1, $array['user_id']);
        $this->assertSame('test@example.com', $array['email']);
    }

    #[Test]
    public function it_deserializes_from_array(): void
    {
        $original = new TestUserRegistered(1, 'test@example.com');
        $array = $original->toArray();

        $restored = TestUserRegistered::fromArray($array);

        $this->assertSame($original->eventId(), $restored->eventId());
        $this->assertSame($original->aggregateId(), $restored->aggregateId());
        $this->assertSame($original->getUserId(), $restored->getUserId());
        $this->assertSame($original->getEmail(), $restored->getEmail());
        $this->assertEquals($original->occurredAt(), $restored->occurredAt());
    }

    #[Test]
    public function it_supports_string_aggregate_id(): void
    {
        $event = new class('uuid-123', 'test@example.com') extends DomainEvent
        {
            public function __construct(
                private readonly string $userId,
                private readonly string $email,
            ) {
                parent::__construct($userId);
            }

            public static function eventName(): string
            {
                return 'test.event';
            }

            public function toArray(): array
            {
                return parent::toArray();
            }

            public static function fromArray(array $data): static
            {
                return new self($data['aggregate_id'], '');
            }
        };

        $this->assertSame('uuid-123', $event->aggregateId());
    }

    #[Test]
    public function it_can_be_dispatched(): void
    {
        // This test verifies the event uses Laravel's Dispatchable trait
        $event = new TestUserRegistered(1, 'test@example.com');

        // The event should have the dispatch method from Dispatchable trait
        $this->assertTrue(method_exists($event, 'dispatch'));
    }
}
