<?php

declare(strict_types=1);

namespace Sonnenglas\MyDHL\ValueObjects;

class ValueAddedService
{
    public function __construct(
        private string $serviceCode,
        private ?DangerousGood $dangerousGood = null,
        private int $value = 0,
        private string $currency = '',
        private string $method = '',
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function getAsArray(): array
    {
        $result = ['serviceCode' => $this->serviceCode];

        if ($this->dangerousGood !== null) {
            $result['dangerousGoods'] = [$this->dangerousGood->getAsArray()];
        }

        if ($this->value !== 0) {
            $result['value'] = $this->value;
        }

        if ($this->currency !== '') {
            $result['currency'] = $this->currency;
        }

        if ($this->method !== '') {
            $result['method'] = $this->method;
        }

        return $result;
    }
}
