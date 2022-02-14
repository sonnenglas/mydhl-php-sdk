<?php

declare(strict_types=1);

namespace Sonnenglas\MyDHL\ValueObjects;

use Sonnenglas\MyDHL\Exceptions\InvalidAddressException;

class Account
{
    private const ALLOWED_TYPE_CODES = [
        "shipper",
        "payer",
        "duties-taxes",
    ];

    public function __construct(
        private string $typeCode,
        private string $number,
    ) {
        $this->validateData();
    }

    public function getNumber(): string
    {
        return $this->number;
    }

    public function getAsArray(): array
    {
        return [
            'typeCode' => $this->typeCode,
            'number' => $this->number,
        ];
    }

    protected function validateData(): void
    {
        if (!in_array($this->typeCode, self::ALLOWED_TYPE_CODES, true)) {
            $errMsg = "Incorrect account type code. Allowed codes: ";
            $errMsg .= implode(', ', self::ALLOWED_TYPE_CODES);

            throw new InvalidAddressException($errMsg);
        }

        if (strlen($this->number) === 0) {
            throw new InvalidAddressException("Account numebr must not be empty.");
        }
    }
}
