<?php

declare(strict_types=1);

namespace Sonnenglas\MyDHL\ValueObjects;

use Sonnenglas\MyDHL\Exceptions\InvalidArgumentException;

/**
 * Tax/regulatory identifier attached to a customer (shipper, receiver, importer,
 * exporter, ...). Common type codes: VAT, EORI, IOSS, OSS, FTZ, SDT, MRN, EIN.
 */
final class RegistrationNumber
{
    public const TYPE_VAT = 'VAT';
    public const TYPE_EORI = 'EORI';
    public const TYPE_IOSS = 'IOSS';
    public const TYPE_OSS = 'OSS';

    public function __construct(
        public readonly string $typeCode,
        public readonly string $number,
        public readonly string $issuerCountryCode,
    ) {
        if ($this->typeCode === '') {
            throw new InvalidArgumentException('RegistrationNumber typeCode must not be empty.');
        }

        if ($this->number === '') {
            throw new InvalidArgumentException('RegistrationNumber number must not be empty.');
        }

        if (strlen($this->issuerCountryCode) !== 2) {
            throw new InvalidArgumentException('RegistrationNumber issuerCountryCode must be a 2-letter ISO code.');
        }
    }

    /**
     * @return array{typeCode: string, number: string, issuerCountryCode: string}
     */
    public function toArray(): array
    {
        return [
            'typeCode' => $this->typeCode,
            'number' => $this->number,
            'issuerCountryCode' => strtoupper($this->issuerCountryCode),
        ];
    }
}
