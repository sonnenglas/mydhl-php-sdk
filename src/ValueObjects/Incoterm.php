<?php

declare(strict_types=1);

namespace Sonnenglas\MyDHL\ValueObjects;

use Sonnenglas\MyDHL\Exceptions\InvalidArgumentException;

class Incoterm
{
    private const ALLOWED_KEYWORDS = [
        'EXW', // ExWorks
        'FCA', // Free Carrier
        'CPT', // Carriage Paid To
        'CIP', // Carriage and Insurance Paid To
        'DPU', // Delivered at Place Unloaded
        'DAP', // Delivered at Place
        'DDP', // Delivered Duty Paid
        'FAS', // Free Alongside Ship
        'FOB', // Free on Board
        'CFR', // Cost and Freight
        'CIF', // Cost, Insurance and Freight
    ];

    /**
     * @param string $incoterm
     * @throws InvalidArgumentException
     */
    public function __construct(
        private string $incoterm,
    ) {
        $this->validate($this->incoterm);
    }

    private function validate(string $incoterm): void
    {
        if (!in_array($incoterm,self::ALLOWED_KEYWORDS)) {
            throw new InvalidArgumentException("Wrong Incoterm used. Allowed terms: ". implode(', ', self::ALLOWED_KEYWORDS));
        }
    }

    public function __toString(): string
    {
        return $this->incoterm;
    }
}
