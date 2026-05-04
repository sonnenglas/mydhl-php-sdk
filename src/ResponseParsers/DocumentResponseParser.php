<?php

declare(strict_types=1);

namespace Sonnenglas\MyDHL\ResponseParsers;

use Sonnenglas\MyDHL\Internal\Cast;
use Sonnenglas\MyDHL\ValueObjects\DhlDocument;

/**
 * Shared parser for any DHL endpoint that returns `{documents: [...]}` with
 * base64-encoded content. Used by ImageService and ProofOfDeliveryService.
 */
final class DocumentResponseParser
{
    /**
     * @param array<string, mixed> $response
     */
    public function __construct(private readonly array $response)
    {
    }

    /**
     * @return list<DhlDocument>
     */
    public function parse(): array
    {
        $documents = $this->response['documents'] ?? [];
        if (!is_array($documents)) {
            return [];
        }

        $result = [];
        foreach ($documents as $document) {
            if (!is_array($document)) {
                continue;
            }
            /** @var array<string, mixed> $document */
            $result[] = $this->parseDocument($document);
        }

        return $result;
    }

    /**
     * @param array<string, mixed> $document
     */
    private function parseDocument(array $document): DhlDocument
    {
        $rawContent = Cast::string($document['content'] ?? '');
        $decoded = base64_decode($rawContent, true);

        return new DhlDocument(
            typeCode: Cast::string($document['typeCode'] ?? ''),
            encodingFormat: Cast::string($document['encodingFormat'] ?? ''),
            content: $decoded === false ? '' : $decoded,
            originalContent: $rawContent,
            shipmentTrackingNumber: isset($document['shipmentTrackingNumber'])
                ? Cast::string($document['shipmentTrackingNumber'])
                : null,
            function: isset($document['function']) ? Cast::string($document['function']) : null,
        );
    }
}
