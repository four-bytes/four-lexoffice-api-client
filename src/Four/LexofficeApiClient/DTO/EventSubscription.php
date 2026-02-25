<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\DTO;

readonly class EventSubscription
{
    // Verfügbare Event Types
    public const EVENT_INVOICE_CREATED                   = 'invoice.created';
    public const EVENT_INVOICE_CHANGED                   = 'invoice.changed';
    public const EVENT_INVOICE_DELETED                   = 'invoice.deleted';
    public const EVENT_INVOICE_STATUS_CHANGED            = 'invoice.status.changed';
    public const EVENT_CONTACT_CREATED                   = 'contact.created';
    public const EVENT_CONTACT_CHANGED                   = 'contact.changed';
    public const EVENT_CONTACT_DELETED                   = 'contact.deleted';
    public const EVENT_ARTICLE_CREATED                   = 'article.created';
    public const EVENT_ARTICLE_CHANGED                   = 'article.changed';
    public const EVENT_ARTICLE_DELETED                   = 'article.deleted';
    public const EVENT_CREDIT_NOTE_CREATED               = 'credit-note.created';
    public const EVENT_CREDIT_NOTE_STATUS_CHANGED        = 'credit-note.status.changed';
    public const EVENT_PAYMENT_CHANGED                   = 'payment.changed';
    public const EVENT_QUOTATION_CREATED                 = 'quotation.created';
    public const EVENT_QUOTATION_STATUS_CHANGED          = 'quotation.status.changed';
    public const EVENT_ORDER_CONFIRMATION_CREATED        = 'order-confirmation.created';
    public const EVENT_ORDER_CONFIRMATION_STATUS_CHANGED = 'order-confirmation.status.changed';
    public const EVENT_DELIVERY_NOTE_CREATED             = 'delivery-note.created';
    public const EVENT_DELIVERY_NOTE_STATUS_CHANGED      = 'delivery-note.status.changed';
    public const EVENT_DUNNING_CREATED                   = 'dunning.created';

    /**
     * @param string[] $eventTypes
     */
    public function __construct(
        public readonly string $callbackUrl,
        public readonly array $eventTypes,
        public readonly bool $active = true,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toApiArray(): array
    {
        return [
            'callbackUrl' => $this->callbackUrl,
            'eventTypes'  => $this->eventTypes,
            'active'      => $this->active,
        ];
    }
}
