<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\DTO;

readonly class Contact
{
    /**
     * @param ContactAddress[] $billingAddresses
     * @param ContactAddress[] $shippingAddresses
     * @param string[]         $emailsBusiness
     * @param string[]         $emailsOffice
     * @param string[]         $emailsPrivate
     * @param string[]         $emailsOther
     * @param string[]         $phonesBusiness
     * @param string[]         $phonesOffice
     * @param string[]         $phonesMobile
     * @param string[]         $phonesPrivate
     * @param string[]         $phonesFax
     * @param string[]         $phonesOther
     */
    public function __construct(
        public int $version = 0,
        public bool $isCustomer = true,
        public bool $isVendor = false,
        public ?ContactCompany $company = null,
        public ?ContactPerson $person = null,
        public array $billingAddresses = [],
        public array $shippingAddresses = [],
        public array $emailsBusiness = [],
        public array $emailsOffice = [],
        public array $emailsPrivate = [],
        public array $emailsOther = [],
        public array $phonesBusiness = [],
        public array $phonesOffice = [],
        public array $phonesMobile = [],
        public array $phonesPrivate = [],
        public array $phonesFax = [],
        public array $phonesOther = [],
        public string $note = '',
        public ?string $id = null,
        public bool $archived = false,
        public ?string $xRechnungBuyerReference = null,
        public ?string $xRechnungVendorNumber = null,
    ) {}

    /** @return array<string, mixed> */
    public function toApiArray(): array
    {
        $roles = [];
        if ($this->isCustomer) $roles['customer'] = new \stdClass();
        if ($this->isVendor)   $roles['vendor']   = new \stdClass();

        $data = [
            'version' => $this->version,
            'roles'   => $roles,
        ];

        if ($this->company !== null) {
            $data['company'] = $this->company->toApiArray();
        }
        if ($this->person !== null) {
            $data['person'] = [
                'salutation' => $this->person->salutation,
                'firstName'  => $this->person->firstName,
                'lastName'   => $this->person->lastName,
            ];
        }

        $addresses = [];
        if ($this->billingAddresses !== []) {
            $addresses['billing'] = array_map(fn(ContactAddress $a) => $a->toApiArray(), $this->billingAddresses);
        }
        if ($this->shippingAddresses !== []) {
            $addresses['shipping'] = array_map(fn(ContactAddress $a) => $a->toApiArray(), $this->shippingAddresses);
        }
        if ($addresses !== []) $data['addresses'] = $addresses;

        $emails = [];
        if ($this->emailsBusiness !== []) $emails['business'] = $this->emailsBusiness;
        if ($this->emailsOffice !== [])   $emails['office']   = $this->emailsOffice;
        if ($this->emailsPrivate !== [])  $emails['private']  = $this->emailsPrivate;
        if ($this->emailsOther !== [])    $emails['other']    = $this->emailsOther;
        if ($emails !== []) $data['emailAddresses'] = $emails;

        $phones = [];
        if ($this->phonesBusiness !== []) $phones['business'] = $this->phonesBusiness;
        if ($this->phonesOffice !== [])   $phones['office']   = $this->phonesOffice;
        if ($this->phonesMobile !== [])   $phones['mobile']   = $this->phonesMobile;
        if ($this->phonesPrivate !== [])  $phones['private']  = $this->phonesPrivate;
        if ($this->phonesFax !== [])      $phones['fax']      = $this->phonesFax;
        if ($this->phonesOther !== [])    $phones['other']    = $this->phonesOther;
        if ($phones !== []) $data['phoneNumbers'] = $phones;

        if ($this->note !== '') $data['note'] = $this->note;

        if ($this->xRechnungBuyerReference !== null || $this->xRechnungVendorNumber !== null) {
            $data['xRechnung'] = [
                'buyerReference'         => $this->xRechnungBuyerReference ?? '',
                'vendorNumberAtCustomer' => $this->xRechnungVendorNumber ?? '',
            ];
        }

        return $data;
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $roles = $data['roles'] ?? [];

        $company = isset($data['company']) ? ContactCompany::fromArray($data['company']) : null;
        $person  = isset($data['person'])  ? ContactPerson::fromArray($data['person'])   : null;

        $billing  = array_map(static fn(array $a) => ContactAddress::fromArray($a), $data['addresses']['billing']  ?? []);
        $shipping = array_map(static fn(array $a) => ContactAddress::fromArray($a), $data['addresses']['shipping'] ?? []);

        $emails = $data['emailAddresses'] ?? [];
        $phones = $data['phoneNumbers']   ?? [];
        $xr     = $data['xRechnung']      ?? [];

        return new self(
            version:                 $data['version'] ?? 0,
            isCustomer:              isset($roles['customer']),
            isVendor:                isset($roles['vendor']),
            company:                 $company,
            person:                  $person,
            billingAddresses:        $billing,
            shippingAddresses:       $shipping,
            emailsBusiness:          $emails['business'] ?? [],
            emailsOffice:            $emails['office']   ?? [],
            emailsPrivate:           $emails['private']  ?? [],
            emailsOther:             $emails['other']    ?? [],
            phonesBusiness:          $phones['business'] ?? [],
            phonesOffice:            $phones['office']   ?? [],
            phonesMobile:            $phones['mobile']   ?? [],
            phonesPrivate:           $phones['private']  ?? [],
            phonesFax:               $phones['fax']      ?? [],
            phonesOther:             $phones['other']    ?? [],
            note:                    $data['note']       ?? '',
            id:                      $data['id']         ?? null,
            archived:                $data['archived']   ?? false,
            xRechnungBuyerReference: $xr['buyerReference']        ?? null,
            xRechnungVendorNumber:   $xr['vendorNumberAtCustomer'] ?? null,
        );
    }
}
