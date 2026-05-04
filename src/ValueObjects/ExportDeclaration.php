<?php

declare(strict_types=1);

namespace Sonnenglas\MyDHL\ValueObjects;

use Sonnenglas\MyDHL\Exceptions\InvalidArgumentException;

/**
 * Container for `content.exportDeclaration` — required by DHL when the
 * shipment is customs-declarable (cross-border with goods of value).
 */
final class ExportDeclaration
{
    public const SHIPMENT_TYPE_PERSONAL = 'personal';
    public const SHIPMENT_TYPE_COMMERCIAL = 'commercial';

    /**
     * @param array<int, mixed> $lineItems Validated at runtime to be non-empty list of LineItem.
     */
    public function __construct(
        public readonly array $lineItems,
        public readonly ?Invoice $invoice = null,
        public readonly ?string $exportReason = null,
        public readonly ?string $exportReasonType = null,
        public readonly ?string $shipmentType = null,
        public readonly ?string $placeOfIncoterm = null,
    ) {
        if ($lineItems === []) {
            throw new InvalidArgumentException('ExportDeclaration requires at least one line item.');
        }

        foreach ($lineItems as $item) {
            if (!$item instanceof LineItem) {
                throw new InvalidArgumentException('lineItems must contain only LineItem instances.');
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $items = [];
        foreach ($this->lineItems as $item) {
            assert($item instanceof LineItem);
            $items[] = $item->toArray();
        }

        $result = ['lineItems' => $items];

        if ($this->invoice !== null) {
            $result['invoice'] = $this->invoice->toArray();
        }

        foreach ([
            'exportReason' => $this->exportReason,
            'exportReasonType' => $this->exportReasonType,
            'shipmentType' => $this->shipmentType,
            'placeOfIncoterm' => $this->placeOfIncoterm,
        ] as $key => $value) {
            if ($value !== null) {
                $result[$key] = $value;
            }
        }

        return $result;
    }
}
