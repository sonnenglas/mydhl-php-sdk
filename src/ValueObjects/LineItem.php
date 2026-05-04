<?php

declare(strict_types=1);

namespace Sonnenglas\MyDHL\ValueObjects;

use Sonnenglas\MyDHL\Exceptions\InvalidArgumentException;

/**
 * Single line in `exportDeclaration.lineItems[]` describing a customs item.
 */
final class LineItem
{
    public const REASON_PERMANENT = 'permanent';
    public const REASON_TEMPORARY = 'temporary';
    public const REASON_RETURN = 'return';
    public const REASON_SAMPLE = 'sample';
    public const REASON_GIFT = 'gift';
    public const REASON_COMMERCIAL = 'commercial_purpose_or_sale';

    public const UNIT_BOX = 'BOX';
    public const UNIT_PIECES = 'PCS';
    public const UNIT_KG = 'KG';
    public const UNIT_EACH = 'EA';

    public function __construct(
        public readonly int $number,
        public readonly string $description,
        public readonly float $price,
        public readonly int $quantityValue,
        public readonly string $quantityUnit,
        public readonly string $manufacturerCountry,
        public readonly ?float $netWeight = null,
        public readonly ?float $grossWeight = null,
        public readonly ?string $exportReasonType = null,
        public readonly ?bool $isTaxesPaid = null,
    ) {
        if ($number < 1 || $number > 999) {
            throw new InvalidArgumentException('LineItem number must be between 1 and 999.');
        }

        if ($description === '' || strlen($description) > 512) {
            throw new InvalidArgumentException('LineItem description must be 1-512 characters.');
        }

        if ($price < 0.0) {
            throw new InvalidArgumentException('LineItem price must be non-negative.');
        }

        if ($quantityValue < 1) {
            throw new InvalidArgumentException('LineItem quantityValue must be at least 1.');
        }

        if (strlen($manufacturerCountry) !== 2) {
            throw new InvalidArgumentException('LineItem manufacturerCountry must be a 2-letter ISO code.');
        }

        if ($netWeight === null && $grossWeight === null) {
            throw new InvalidArgumentException('LineItem must specify netWeight or grossWeight (or both).');
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $weight = array_filter([
            'netValue' => $this->netWeight,
            'grossValue' => $this->grossWeight,
        ], static fn (?float $v): bool => $v !== null);

        return array_filter([
            'number' => $this->number,
            'description' => $this->description,
            'price' => $this->price,
            'quantity' => [
                'value' => $this->quantityValue,
                'unitOfMeasurement' => $this->quantityUnit,
            ],
            'manufacturerCountry' => strtoupper($this->manufacturerCountry),
            'weight' => $weight !== [] ? $weight : null,
            'exportReasonType' => $this->exportReasonType,
            'isTaxesPaid' => $this->isTaxesPaid,
        ], static fn (mixed $v): bool => $v !== null);
    }
}
