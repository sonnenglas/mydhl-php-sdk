<?php

declare(strict_types=1);

namespace Sonnenglas\MyDHL\ValueObjects;

use Sonnenglas\MyDHL\Exceptions\InvalidArgumentException;

/**
 * Top-level rendering options for the documents DHL produces (label,
 * waybillDoc, invoice, shipment receipt). All fields optional — defaults
 * apply when omitted.
 */
final class OutputImageProperties
{
    public const ENCODING_PDF = 'pdf';
    public const ENCODING_ZPL = 'zpl';
    public const ENCODING_LP2 = 'lp2';
    public const ENCODING_EPL = 'epl';

    private const ALLOWED_DPI = [200, 300];
    private const ALLOWED_ENCODING = [self::ENCODING_PDF, self::ENCODING_ZPL, self::ENCODING_LP2, self::ENCODING_EPL];

    /**
     * @param array<int, mixed> $imageOptions Validated at runtime to be a list of ImageOption.
     */
    public function __construct(
        public readonly ?int $printerDPI = null,
        public readonly ?string $encodingFormat = null,
        public readonly array $imageOptions = [],
        public readonly ?bool $splitTransportAndWaybillDocLabels = null,
        public readonly ?bool $allDocumentsInOneImage = null,
    ) {
        if ($this->printerDPI !== null && !in_array($this->printerDPI, self::ALLOWED_DPI, true)) {
            throw new InvalidArgumentException('printerDPI must be 200 or 300.');
        }

        if ($this->encodingFormat !== null && !in_array($this->encodingFormat, self::ALLOWED_ENCODING, true)) {
            throw new InvalidArgumentException(
                'encodingFormat must be one of: ' . implode(', ', self::ALLOWED_ENCODING) . '.'
            );
        }

        foreach ($this->imageOptions as $option) {
            if (!$option instanceof ImageOption) {
                throw new InvalidArgumentException('imageOptions must contain only ImageOption instances.');
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $result = array_filter([
            'printerDPI' => $this->printerDPI,
            'encodingFormat' => $this->encodingFormat,
            'splitTransportAndWaybillDocLabels' => $this->splitTransportAndWaybillDocLabels,
            'allDocumentsInOneImage' => $this->allDocumentsInOneImage,
        ], static fn (mixed $v): bool => $v !== null);

        if ($this->imageOptions !== []) {
            $options = [];
            foreach ($this->imageOptions as $option) {
                assert($option instanceof ImageOption);
                $options[] = $option->toArray();
            }
            $result['imageOptions'] = $options;
        }

        return $result;
    }
}
