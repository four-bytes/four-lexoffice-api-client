<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\DTO;

readonly class ContactAddress
{
    public function __construct(
        public string $street = '',
        public string $supplement = '',
        public string $zip = '',
        public string $city = '',
        public string $countryCode = 'DE',
    ) {
    }

    /**
     * @return array<string, string>
     */
    public function toApiArray(): array
    {
        $data = [
            'street'      => $this->street,
            'zip'         => $this->zip,
            'city'        => $this->city,
            'countryCode' => $this->countryCode,
        ];

        if ($this->supplement !== '') {
            $data['supplement'] = $this->supplement;
        }

        return $data;
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): static
    {
        return new static(
            street:      $data['street'] ?? '',
            supplement:  $data['supplement'] ?? '',
            zip:         $data['zip'] ?? '',
            city:        $data['city'] ?? '',
            countryCode: $data['countryCode'] ?? 'DE',
        );
    }
}
