<?php

declare(strict_types=1);

namespace Sonnenglas\MyDHL\ValueObjects;

use Sonnenglas\MyDHL\Exceptions\InvalidArgumentException;

/**
 * Per-document rendering options inside `outputImageProperties.imageOptions[]`.
 * Used to pin label/waybill/invoice templates and request specific extras.
 */
final class ImageOption
{
    public const TYPE_LABEL = 'label';
    public const TYPE_WAYBILL = 'waybillDoc';
    public const TYPE_INVOICE = 'invoice';
    public const TYPE_QR_CODE = 'qr-code';
    public const TYPE_SHIPMENT_RECEIPT = 'shipmentReceipt';

    public const INVOICE_COMMERCIAL = 'commercial';
    public const INVOICE_PROFORMA = 'proforma';
    public const INVOICE_RETURNS = 'returns';

    public function __construct(
        public readonly string $typeCode,
        public readonly ?string $templateName = null,
        public readonly ?bool $isRequested = null,
        public readonly ?bool $hideAccountNumber = null,
        public readonly ?int $numberOfCopies = null,
        public readonly ?string $invoiceType = null,
        public readonly ?string $languageCode = null,
        public readonly ?string $languageCountryCode = null,
        public readonly ?string $encodingFormat = null,
    ) {
        if ($this->typeCode === '') {
            throw new InvalidArgumentException('ImageOption typeCode must not be empty.');
        }

        if ($this->numberOfCopies !== null && ($this->numberOfCopies < 1 || $this->numberOfCopies > 2)) {
            throw new InvalidArgumentException('ImageOption numberOfCopies must be 1 or 2.');
        }

        if ($this->languageCode !== null && strlen($this->languageCode) !== 3) {
            throw new InvalidArgumentException('ImageOption languageCode must be 3 letters (e.g. "eng").');
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'typeCode' => $this->typeCode,
            'templateName' => $this->templateName,
            'isRequested' => $this->isRequested,
            'hideAccountNumber' => $this->hideAccountNumber,
            'numberOfCopies' => $this->numberOfCopies,
            'invoiceType' => $this->invoiceType,
            'languageCode' => $this->languageCode,
            'languageCountryCode' => $this->languageCountryCode,
            'encodingFormat' => $this->encodingFormat,
        ], static fn (mixed $v): bool => $v !== null);
    }
}
