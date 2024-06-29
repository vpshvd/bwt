<?php declare(strict_types=1);

namespace App\Service;

use App\ApiClient\ApiClientInterface;
use App\Exception\UnableToDecodeAPIResponseException;
use App\Exception\UnsupportedCurrencyException;

readonly class CurrencyConverter
{
    private const int EXCHANGE_RATE_CACHE_TTL = 3600; // one minute

    public function __construct(
        private ApiClientInterface $apiClient,
        private string $apiUrl,
        private string $apiKey
    ) {
    }

    /**
     * @throws UnsupportedCurrencyException
     * @throws UnableToDecodeAPIResponseException
     */
    public function convert(float $amount, string $fromCurrency, string $toCurrency = 'EUR'): float
    {
        $rates = $this->getRates();

        if ($fromCurrency !== 'EUR') {
            if (!isset($rates[$fromCurrency])) {
                throw new UnsupportedCurrencyException($fromCurrency);
            }
            $amount /= $rates[$fromCurrency];
        }

        if ($toCurrency !== 'EUR') {
            if (!isset($rates[$toCurrency])) {
                throw new UnsupportedCurrencyException($toCurrency);
            }
            $amount *= $rates[$toCurrency];
        }

        return $this->ceilByCents($amount);
    }


    /**
     * @throws UnableToDecodeAPIResponseException
     */
    private function getRates(): array
    {
        $options = [
            'query' => [
                'access_key' => $this->apiKey,
            ],
        ];

        $response = $this->apiClient->get($this->apiUrl, self::EXCHANGE_RATE_CACHE_TTL, $options);

        try {
            $data = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new UnableToDecodeAPIResponseException('Failed to decode exchange rates API response', $e);
        }

        return $data['rates'] ?? [];
    }

    private function ceilByCents(float $amount): float
    {
        return ceil($amount * 100) / 100;
    }
}
