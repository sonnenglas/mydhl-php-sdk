<?php

declare(strict_types=1);

namespace Sonnenglas\MyDHL\ValueObjects;

class Shipment
{
    /**
     * @param list<mixed> $warnings
     * @param list<array<string, mixed>> $packages
     * @param list<array<string, mixed>> $documents
     * @param list<array<string, mixed>> $shipmentDetails
     * @param list<array<string, mixed>> $shipmentCharges
     */
    public function __construct(
        private string $shipmentTrackingNumber,
        private string $labelPdf,
        private ?string $dispatchConfirmationNumber = null,
        private ?string $cancelPickupUrl = null,
        private ?string $trackingUrl = null,
        private array $warnings = [],
        private array $packages = [],
        private array $documents = [],
        private array $shipmentDetails = [],
        private array $shipmentCharges = [],
    ) {
    }

    public function getLabelPdf(): string
    {
        return $this->labelPdf;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getShipmentCharges(): array
    {
        return $this->shipmentCharges;
    }

    public function getShipmentTrackingNumber(): string
    {
        return $this->shipmentTrackingNumber;
    }

    public function getCancelPickupUrl(): ?string
    {
        return $this->cancelPickupUrl;
    }

    public function getTrackingUrl(): ?string
    {
        return $this->trackingUrl;
    }

    public function getDispatchConfirmationNumber(): ?string
    {
        return $this->dispatchConfirmationNumber;
    }

    /**
     * @return list<mixed>
     */
    public function getWarnings(): array
    {
        return $this->warnings;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getPackages(): array
    {
        return $this->packages;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getDocuments(): array
    {
        return $this->documents;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getShipmentDetails(): array
    {
        return $this->shipmentDetails;
    }

    public function __toString(): string
    {
        return json_encode($this->getAsArray(), JSON_THROW_ON_ERROR);
    }

    /**
     * @return array<string, mixed>
     */
    public function getAsArray(): array
    {
        $values = get_object_vars($this);

        // Skip the binary label — it duplicates $values['documents'][0]['content'].
        unset($values['labelPdf']);

        return $values;
    }
}
