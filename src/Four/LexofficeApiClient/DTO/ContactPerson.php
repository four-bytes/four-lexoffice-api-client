<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\DTO;

readonly class ContactPerson
{
    public function __construct(
        public string $lastName,
        public string $firstName = '',
        public string $salutation = '',
        public bool $primary = true,
        public string $emailAddress = '',
        public string $phoneNumber = '',
    ) {}

    /** @return array<string, mixed> */
    public function toApiArray(): array
    {
        $data = ['lastName' => $this->lastName, 'primary' => $this->primary];
        if ($this->firstName !== '') $data['firstName'] = $this->firstName;
        if ($this->salutation !== '') $data['salutation'] = $this->salutation;
        if ($this->emailAddress !== '') $data['emailAddress'] = $this->emailAddress;
        if ($this->phoneNumber !== '') $data['phoneNumber'] = $this->phoneNumber;
        return $data;
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): static
    {
        return new static(
            lastName:     $data['lastName'] ?? '',
            firstName:    $data['firstName'] ?? '',
            salutation:   $data['salutation'] ?? '',
            primary:      $data['primary'] ?? true,
            emailAddress: $data['emailAddress'] ?? '',
            phoneNumber:  $data['phoneNumber'] ?? '',
        );
    }
}
