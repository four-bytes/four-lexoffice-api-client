<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\Tests\DTO;

use Four\LexofficeApiClient\DTO\EventSubscription;
use PHPUnit\Framework\TestCase;

class EventSubscriptionTest extends TestCase
{
    public function testToApiArrayProducesCorrectStructure(): void
    {
        $subscription = new EventSubscription(
            callbackUrl: 'https://example.com/webhook',
            eventTypes:  [
                EventSubscription::EVENT_INVOICE_CREATED,
                EventSubscription::EVENT_PAYMENT_CHANGED,
            ],
        );

        $data = $subscription->toApiArray();

        self::assertSame('https://example.com/webhook', $data['callbackUrl']);
        self::assertCount(2, $data['eventTypes']);
        self::assertContains('invoice.created', $data['eventTypes']);
        self::assertContains('payment.changed', $data['eventTypes']);
        self::assertTrue($data['active']);
    }

    public function testActiveCanBeSetToFalse(): void
    {
        $subscription = new EventSubscription(
            callbackUrl: 'https://example.com/hook',
            eventTypes:  [EventSubscription::EVENT_CONTACT_CREATED],
            active:      false,
        );

        $data = $subscription->toApiArray();
        self::assertFalse($data['active']);
    }

    public function testEventTypeConstants(): void
    {
        self::assertSame('invoice.created', EventSubscription::EVENT_INVOICE_CREATED);
        self::assertSame('invoice.status.changed', EventSubscription::EVENT_INVOICE_STATUS_CHANGED);
        self::assertSame('contact.deleted', EventSubscription::EVENT_CONTACT_DELETED);
        self::assertSame('article.changed', EventSubscription::EVENT_ARTICLE_CHANGED);
        self::assertSame('credit-note.created', EventSubscription::EVENT_CREDIT_NOTE_CREATED);
        self::assertSame('payment.changed', EventSubscription::EVENT_PAYMENT_CHANGED);
        self::assertSame('dunning.created', EventSubscription::EVENT_DUNNING_CREATED);
        self::assertSame('delivery-note.status.changed', EventSubscription::EVENT_DELIVERY_NOTE_STATUS_CHANGED);
    }

    public function testFromArrayParsesAllFields(): void
    {
        $subscription = EventSubscription::fromArray([
            'callbackUrl' => 'https://example.com/webhook',
            'eventTypes'  => [
                3 => EventSubscription::EVENT_INVOICE_CREATED,
                7 => EventSubscription::EVENT_PAYMENT_CHANGED,
            ],
            'active' => false,
        ]);

        self::assertSame('https://example.com/webhook', $subscription->callbackUrl);
        self::assertSame(
            [EventSubscription::EVENT_INVOICE_CREATED, EventSubscription::EVENT_PAYMENT_CHANGED],
            $subscription->eventTypes,
        );
        self::assertFalse($subscription->active);
    }

    public function testFromArrayUsesDefaultsForMissingFields(): void
    {
        $subscription = EventSubscription::fromArray([]);

        self::assertSame('', $subscription->callbackUrl);
        self::assertSame([], $subscription->eventTypes);
        self::assertTrue($subscription->active);
    }
}
