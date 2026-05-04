<?php

declare(strict_types=1);

namespace Sonnenglas\MyDHL\ValueObjects;

use Sonnenglas\MyDHL\Exceptions\InvalidArgumentException;

/**
 * Customer-supplied reference (PO number, order ID, MRN, etc.) attached to a
 * shipment, line item, invoice, or piece.
 *
 * @see https://developer.dhl.com/api-reference/dhl-express-mydhl-api — schema
 *      `supermodelIoLogisticsExpressReference`. The `typeCode` enum is large
 *      (CU, CO, AAO, FF, FN, IBC, LLR, OBC, PRN, ACP, ACS, ACR, CDN, STD,
 *      AFM, ...). We don't enforce it client-side because the list grows.
 */
final class CustomerReference
{
    public const TYPE_CONSIGNOR = 'CU';
    public const TYPE_BUYER_ORDER = 'CO';
    public const TYPE_RECEIVER_REFERENCE = 'AAO';
    public const TYPE_CUSTOMS_DECLARATION = 'CDN';

    public function __construct(
        public readonly string $value,
        public readonly string $typeCode = self::TYPE_CONSIGNOR,
    ) {
        if ($this->value === '' || strlen($this->value) > 35) {
            throw new InvalidArgumentException('Customer reference value must be 1-35 characters.');
        }

        if ($this->typeCode === '') {
            throw new InvalidArgumentException('Customer reference typeCode must not be empty.');
        }
    }

    /**
     * @return array{value: string, typeCode: string}
     */
    public function toArray(): array
    {
        return [
            'value' => $this->value,
            'typeCode' => $this->typeCode,
        ];
    }
}
