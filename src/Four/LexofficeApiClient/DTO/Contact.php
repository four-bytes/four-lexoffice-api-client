<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\DTO;

readonly class Contact
{
    public function __construct(
        public ?string $id = null,
        public int $version = 0,
        public bool $isCompany = false,
        public string $firstName = '',
        public string $lastName = '',
        public string $companyName = '',
        public ?string $vatId = null,
        public string $email = '',
        public string $street = '',
        public string $supplement = '',
        public string $zip = '',
        public string $city = '',
        public string $countryCode = 'DE',
        public bool $isVendor = false,
    ) {
    }

    /**
     * Wandelt den Contact in das API-Format von lexoffice um.
     *
     * @return array<string, mixed>
     */
    public function toApiArray(): array
    {
        $roles = [
            'customer' => ['number' => ''],
        ];

        if ($this->isVendor) {
            $roles['vendor'] = ['number' => ''];
        }

        $address = [
            'street'      => $this->street,
            'zip'         => $this->zip,
            'city'        => $this->city,
            'countryCode' => $this->countryCode,
        ];

        if ($this->supplement !== '') {
            $address['supplement'] = $this->supplement;
        }

        $data = [
            'version' => $this->version,
            'roles'   => $roles,
            'addresses' => [
                'billing' => [$address],
            ],
        ];

        if ($this->isCompany) {
            $company = ['name' => $this->companyName];
            if ($this->vatId !== null && $this->vatId !== '') {
                $company['vatRegistrationId'] = $this->vatId;
            }
            $data['company'] = $company;
        } else {
            $data['person'] = [
                'name'      => trim($this->firstName . ' ' . $this->lastName),
                'firstName' => $this->firstName,
                'lastName'  => $this->lastName,
            ];
        }

        if ($this->email !== '') {
            $data['emailAddresses'] = [
                'private' => [$this->email],
            ];
        }

        return $data;
    }
}
