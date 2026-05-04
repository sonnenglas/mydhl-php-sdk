<?php

declare(strict_types=1);

namespace Sonnenglas\MyDHL\ValueObjects;

use Sonnenglas\MyDHL\Exceptions\InvalidArgumentException;

final class Package
{
    public function __construct(
        public readonly float $weight,
        public readonly float $height,
        public readonly float $length,
        public readonly float $width,
    ) {
        if ($weight <= 0.0) {
            throw new InvalidArgumentException('Package weight must be greater than zero.');
        }

        foreach (['height' => $height, 'length' => $length, 'width' => $width] as $name => $value) {
            if ($value <= 0.0) {
                throw new InvalidArgumentException("Package {$name} must be greater than zero.");
            }
        }
    }

    public function getWeight(): float
    {
        return $this->weight;
    }

    public function getHeight(): float
    {
        return $this->height;
    }

    public function getLength(): float
    {
        return $this->length;
    }

    public function getWidth(): float
    {
        return $this->width;
    }
}
