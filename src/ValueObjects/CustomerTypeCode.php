<?php

declare(strict_types=1);

namespace Sonnenglas\MyDHL\ValueObjects;

use Sonnenglas\MyDHL\Exceptions\InvalidArgumentException;

class CustomerTypeCode
{
    private const ALLOWED_KEYWORDS = [
        "business",
        "direct_consumer",
        "government",
        "other",
        "private",
        "reseller",
    ];

    /**
     * @param string $typeCode
     * @throws InvalidArgumentException
     */
    public function __construct(
        private string $typeCode,
    ) {
        $this->validate($this->typeCode);
    }

    private function validate(string $typeCode): void
    {
        if (!in_array($typeCode, self::ALLOWED_KEYWORDS, true)) {
            throw new InvalidArgumentException("Wrong customer type code used. Allowed terms: ". implode(', ', self::ALLOWED_KEYWORDS));
        }
    }

    public function __toString(): string
    {
        return $this->typeCode;
    }
}
