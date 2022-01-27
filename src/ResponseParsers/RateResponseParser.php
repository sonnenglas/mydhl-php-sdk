<?php

declare(strict_types=1);

namespace Sonnenglas\MyDHL\ResponseParsers;

use DateTimeImmutable;
use Exception;
use Sonnenglas\MyDHL\Exceptions\TotalPriceNotFoundException;
use Sonnenglas\MyDHL\ValueObjects\Rate;

class RateResponseParser
{
    public const DEFAULT_CURRENCY = 'EUR';

    /**
     * Parse the API rates response and return array of available rates
     * @param array $response
     * @return Rate[]
     * @throws TotalPriceNotFoundException
     */
    public function parse(array $response): array
    {
        $rates = [];

        if (!isset($response['products']) || !is_iterable($response['products'])) {
            return $rates;
        }

        foreach ($response['products'] as $p) {
            $rates[] = $this->parseRate($p);
        }

        return $rates;
    }

    /**
     * @param array $rate
     * @return Rate
     * @throws TotalPriceNotFoundException|Exception
     */
    protected function parseRate(array $rate): Rate
    {
        [$totalPrice, $currency] = $this->parseTotalPrice($rate['totalPrice']);

        $estimatedDeliveryDateAndTime = new DateTimeImmutable($rate['deliveryCapabilities']['estimatedDeliveryDateAndTime']);

        $pricingDate = new DateTimeImmutable($rate['pricingDate']);

        return new Rate(
            productName: (string) $rate['productName'],
            productCode: (string) $rate['productCode'],
            localProductCode: (string) $rate['localProductCode'],
            localProductCountryCode: (string) $rate['localProductCountryCode'],
            isCustomerAgreement: (bool) $rate['isCustomerAgreement'],
            weightVolumetric: (float) $rate['weight']['volumetric'],
            weightProvided: (float) $rate['weight']['provided'],
            totalPrice: (float) $totalPrice,
            currency: (string) $currency,
            estimatedDeliveryDateAndTime: $estimatedDeliveryDateAndTime,
            pricingDate: $pricingDate,
        );
    }

    /**
     * @param array $prices
     * @return array
     * @throws TotalPriceNotFoundException
     */
    protected function parseTotalPrice(array $prices): array
    {
        foreach ($prices as $p) {
            if ($p['currencyType'] === 'BILLC') {
                $price = (float) $p['price'];

                if (!isset($p['priceCurrency']) && $price == 0.00) {
                    return [0, self::DEFAULT_CURRENCY];
                }

                return [$price, (string) $p['priceCurrency']];
            }
        }

        throw new TotalPriceNotFoundException('Total price of type BILLC not found.');
    }
}
