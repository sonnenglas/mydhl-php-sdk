<?php

declare(strict_types=1);

namespace Sonnenglas\MyDHL\ResponseParsers;

use DateTimeImmutable;
use Exception;
use Sonnenglas\MyDHL\Exceptions\TotalPriceNotFoundException;
use Sonnenglas\MyDHL\Internal\Cast;
use Sonnenglas\MyDHL\ValueObjects\Rate;

final class RateResponseParser
{
    public const DEFAULT_CURRENCY = 'EUR';

    /**
     * @param array<string, mixed> $response
     */
    public function __construct(private readonly array $response)
    {
    }

    /**
     * Parses the API rates response and returns the available rates.
     *
     * @return list<Rate>
     * @throws TotalPriceNotFoundException
     */
    public function parse(): array
    {
        $rates = [];

        if (!isset($this->response['products']) || !is_iterable($this->response['products'])) {
            return $rates;
        }

        foreach ($this->response['products'] as $product) {
            if (!is_array($product)) {
                continue;
            }
            /** @var array<string, mixed> $product */
            $rates[] = $this->parseRate($product);
        }

        return $rates;
    }

    /**
     * @param array<string, mixed> $rate
     * @throws TotalPriceNotFoundException|Exception
     */
    private function parseRate(array $rate): Rate
    {
        /** @var list<array<string, mixed>> $prices */
        $prices = $rate['totalPrice'];
        [$totalPrice, $currency] = $this->parseTotalPrice($prices);

        /** @var array{estimatedDeliveryDateAndTime: mixed} $delivery */
        $delivery = $rate['deliveryCapabilities'];
        $estimatedDeliveryDateAndTime = new DateTimeImmutable(Cast::string($delivery['estimatedDeliveryDateAndTime']));

        $pricingDate = new DateTimeImmutable(Cast::string($rate['pricingDate']));

        /** @var array{volumetric: mixed, provided: mixed} $weight */
        $weight = $rate['weight'];

        return new Rate(
            productName: Cast::string($rate['productName']),
            productCode: Cast::string($rate['productCode']),
            localProductCode: Cast::string($rate['localProductCode']),
            localProductCountryCode: Cast::string($rate['localProductCountryCode']),
            isCustomerAgreement: Cast::bool($rate['isCustomerAgreement']),
            weightVolumetric: Cast::float($weight['volumetric']),
            weightProvided: Cast::float($weight['provided']),
            totalPrice: $totalPrice,
            currency: $currency,
            estimatedDeliveryDateAndTime: $estimatedDeliveryDateAndTime,
            pricingDate: $pricingDate,
        );
    }

    /**
     * @param list<array<string, mixed>> $prices
     * @return array{0: float, 1: string}
     * @throws TotalPriceNotFoundException
     */
    private function parseTotalPrice(array $prices): array
    {
        foreach ($prices as $p) {
            if ($p['currencyType'] === 'BILLC') {
                $price = Cast::float($p['price']);

                if (!isset($p['priceCurrency']) && $price === 0.0) {
                    return [0.0, self::DEFAULT_CURRENCY];
                }

                return [$price, Cast::string($p['priceCurrency'])];
            }
        }

        throw new TotalPriceNotFoundException('Total price of type BILLC not found.');
    }
}
