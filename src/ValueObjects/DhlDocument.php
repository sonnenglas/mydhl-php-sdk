<?php

declare(strict_types=1);

namespace Sonnenglas\MyDHL\ValueObjects;

/**
 * Document returned by `/get-image` and `/proof-of-delivery`. The `content` is
 * the decoded binary (base64-decoded by the parser) ready to be written to disk
 * or streamed; `originalContent` keeps the raw base64 string in case callers
 * want to forward it without re-encoding.
 */
final class DhlDocument
{
    public const TYPE_LABEL = 'label';
    public const TYPE_WAYBILL = 'waybill';
    public const TYPE_INVOICE = 'invoice';
    public const TYPE_POD = 'POD';

    public function __construct(
        public readonly string $typeCode,
        public readonly string $encodingFormat,
        public readonly string $content,
        public readonly string $originalContent,
        public readonly ?string $shipmentTrackingNumber = null,
        public readonly ?string $function = null,
    ) {
    }

    public function isPdf(): bool
    {
        return strtolower($this->encodingFormat) === 'pdf';
    }
}
