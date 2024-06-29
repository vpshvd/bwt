<?php declare(strict_types=1);

namespace App\Service;

use App\ApiClient\ApiClientInterface;
use App\DTO\Transaction;
use App\Enum\EUCountry;
use App\Exception\InvalidBINException;
use App\Exception\UnableToDecodeAPIResponseException;
use App\Exception\UnsupportedCurrencyException;

final readonly class TransactionProcessor
{
    private const float EU_COMMISSION_RATE = 0.01;
    private const float NON_EU_COMMISSION_RATE = 0.02;
    private const int BIN_CACHE_TTL = 604800; // one week

    public function __construct(
        private ApiClientInterface $apiClient,
        private CurrencyConverter $currencyConverter,
        private string $apiUrl
    ) {
    }

    /**
     * @throws UnsupportedCurrencyException
     * @throws UnableToDecodeAPIResponseException
     */
    public function processTransactions(string $filePath): array
    {
        $fileContents = file_get_contents($filePath);
        $rows = explode("\n", $fileContents);
        $commissions = [];

        foreach ($rows as $row) {
            if (empty($row)) {
                break;
            }

            $transaction = $this->parseTransaction($row);

            try {
                $isEu = $this->isEu($transaction->bin);
            } catch (InvalidBINException $e) {
                echo sprintf("Skipping invalid BIN: %s (%s)\n", $transaction->bin, $e->getMessage());
                continue;
            }

            $convertedAmount = $this->currencyConverter->convert($transaction->amount, $transaction->currency);

            $commissionRate = $isEu ? self::EU_COMMISSION_RATE : self::NON_EU_COMMISSION_RATE;
            $commission = $convertedAmount * $commissionRate;

            $commissions[] = $commission;
        }

        return $commissions;
    }

    private function parseTransaction(string $row): Transaction
    {
        $parts = explode(',', $row);
        $bin = trim(explode(':', $parts[0])[1], '"');
        $amount = (float)trim(explode(':', $parts[1])[1], '"');
        $currency = trim(explode(':', $parts[2])[1], '"}');

        return new Transaction($bin, $amount, $currency);
    }

    /**
     * @throws InvalidBINException
     * @throws UnableToDecodeAPIResponseException
     */
    private function isEu(string $bin): bool
    {
        $response = $this->apiClient->get(
            url: $this->apiUrl,
            cacheTTL: self::BIN_CACHE_TTL,
            bin: $bin
        );
        try {
            $data = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new UnableToDecodeAPIResponseException('Failed to decode BIN API response', $e);
        }

        if (!isset($data['country']['alpha2'])) {
            throw new InvalidBINException($bin, "Invalid BIN data: missing country alpha2 code");
        }

        $countryCode = $data['country']['alpha2'];

        return EUCountry::tryFrom($countryCode) !== null;
    }
}
