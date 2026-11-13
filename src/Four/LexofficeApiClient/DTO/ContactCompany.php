<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\DTO;

readonly class ContactCompany
{
    /**
     * @param ContactPerson[] $contactPersons
     */
    public function __construct(
        public string $name,
        public string $taxNumber = '',
        public string $vatRegistrationId = '',
        public bool $allowTaxFreeInvoices = false,
        public array $contactPersons = [],
    ) {}

    /** @return array<string, mixed> */
    public function toApiArray(): array
    {
        $data = ['name' => $this->name];
        if ($this->taxNumber !== '') $data['taxNumber'] = $this->taxNumber;
        if ($this->vatRegistrationId !== '') $data['vatRegistrationId'] = $this->vatRegistrationId;
        if ($this->allowTaxFreeInvoices) $data['allowTaxFreeInvoices'] = true;
        if ($this->contactPersons !== []) {
            $data['contactPersons'] = array_map(fn(ContactPerson $p) => $p->toApiArray(), $this->contactPersons);
        }
        return $data;
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $persons = array_map(
            static fn(array $p) => ContactPerson::fromArray($p),
            $data['contactPersons'] ?? [],
        );
        return new self(
            name:                 $data['name'] ?? '',
            taxNumber:            $data['taxNumber'] ?? '',
            vatRegistrationId:    $data['vatRegistrationId'] ?? '',
            allowTaxFreeInvoices: $data['allowTaxFreeInvoices'] ?? false,
            contactPersons:       $persons,
        );
    }
}
