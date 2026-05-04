<?php

declare(strict_types=1);

namespace Sonnenglas\MyDHL\ValueObjects;

use DateTimeImmutable;
use Sonnenglas\MyDHL\Exceptions\InvalidArgumentException;

/**
 * Commercial / proforma invoice metadata embedded in `exportDeclaration.invoice`.
 */
final class Invoice
{
    public function __construct(
        public readonly string $number,
        public readonly DateTimeImmutable $date,
        public readonly ?string $signatureName = null,
        public readonly ?string $signatureTitle = null,
        public readonly ?float $totalNetWeight = null,
        public readonly ?float $totalGrossWeight = null,
    ) {
        if ($this->number === '' || strlen($this->number) > 35) {
            throw new InvalidArgumentException('Invoice number must be 1-35 characters.');
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'number' => $this->number,
            'date' => $this->date->format('Y-m-d'),
            'signatureName' => $this->signatureName,
            'signatureTitle' => $this->signatureTitle,
            'totalNetWeight' => $this->totalNetWeight,
            'totalGrossWeight' => $this->totalGrossWeight,
        ], static fn (mixed $v): bool => $v !== null);
    }
}
