<?php

require __DIR__.'/../vendor/autoload.php';

use App\ApiClient\ApiClient;
use App\Exception\ApiClientException;
use App\Exception\UnableToDecodeAPIResponseException;
use App\Exception\UnsupportedCurrencyException;
use App\Service\CurrencyConverter;
use App\Service\TransactionProcessor;
use Dotenv\Dotenv;
use GuzzleHttp\Client;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

$dotenv = Dotenv::createImmutable(__DIR__.'/../');
$dotenv->load();

$httpClient = new Client();
$cache = new FilesystemAdapter();
$apiClient = new ApiClient($httpClient, $cache);
$exchangeRateApiUrl = $_ENV['EXCHANGE_RATE_API_URL'];
$exchangeRateApiKey = $_ENV['EXCHANGE_RATE_API_KEY'];
$binlistApiUrl = $_ENV['BINLIST_API_URL'];
$currencyConverter = new CurrencyConverter($apiClient, $exchangeRateApiUrl, $exchangeRateApiKey);
$transactionProcessor = new TransactionProcessor($apiClient, $currencyConverter, $binlistApiUrl);
$filePath = $argv[1];

try {
    $commissions = $transactionProcessor->processTransactions($filePath);
    foreach ($commissions as $commission) {
        echo $commission."\n";
    }
} catch (UnableToDecodeAPIResponseException|UnsupportedCurrencyException|ApiClientException $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}
