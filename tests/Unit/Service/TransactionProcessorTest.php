<?php declare(strict_types=1);

namespace Unit\Service;

use JsonException;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use App\Service\TransactionProcessor;
use App\ApiClient\ApiClientInterface;
use App\Service\CurrencyConverter;
use App\Exception\InvalidBINException;
use App\Exception\UnableToDecodeAPIResponseException;
use App\Exception\UnsupportedCurrencyException;

final class TransactionProcessorTest extends TestCase
{
    private ApiClientInterface $apiClient;
    private CurrencyConverter $currencyConverter;
    private TransactionProcessor $transactionProcessor;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->apiClient = $this->createMock(ApiClientInterface::class);
        $this->currencyConverter = $this->createMock(CurrencyConverter::class);
        $this->transactionProcessor = new TransactionProcessor(
            $this->apiClient,
            $this->currencyConverter,
            'https://lookup.binlist.net/'
        );
    }

    /**
     * @throws UnsupportedCurrencyException
     * @throws JsonException
     * @throws UnableToDecodeAPIResponseException
     */
    public function testProcessTransactionsWithValidData(): void
    {
        $filePath = 'test_data.txt';
        file_put_contents($filePath, 'bin: "45717360", amount: "100.00", currency: "EUR"');

        $this->apiClient->method('get')
            ->willReturn(json_encode(['country' => ['alpha2' => 'DE']], JSON_THROW_ON_ERROR));
        $this->currencyConverter->method('convert')
            ->willReturn(100.00);

        $commissions = $this->transactionProcessor->processTransactions($filePath);

        $this->assertCount(1, $commissions);
        $this->assertEquals(1.0, $commissions[0]);

        unlink($filePath);
    }

    /**
     * @throws UnsupportedCurrencyException
     * @throws UnableToDecodeAPIResponseException
     */
    public function testProcessTransactionsWithInvalidBIN(): void
    {
        $filePath = 'test_data.txt';
        file_put_contents($filePath, 'bin: "invalid", amount: "100.00", currency: "EUR"');

        $this->apiClient->method('get')
            ->willThrowException(new InvalidBINException('invalid', 'Invalid BIN data'));

        $commissions = $this->transactionProcessor->processTransactions($filePath);

        $this->assertEmpty($commissions);

        unlink($filePath);
    }

    /**
     * @throws UnableToDecodeAPIResponseException
     * @throws JsonException
     */
    public function testProcessTransactionsWithUnsupportedCurrency(): void
    {
        $filePath = 'test_data.txt';
        file_put_contents($filePath, 'bin: "45717360", amount: "100.00", currency: "XYZ"');

        $this->apiClient->method('get')
            ->willReturn(json_encode(['country' => ['alpha2' => 'DE']], JSON_THROW_ON_ERROR));
        $this->currencyConverter->method('convert')
            ->willThrowException(new UnsupportedCurrencyException('Unsupported currency: XYZ'));

        $this->expectException(UnsupportedCurrencyException::class);
        $this->transactionProcessor->processTransactions($filePath);

        unlink($filePath);
    }

    /**
     * @throws UnsupportedCurrencyException
     */
    public function testProcessTransactionsWithApiError(): void
    {
        $filePath = 'test_data.txt';
        file_put_contents($filePath, 'bin: "45717360", amount: "100.00", currency: "EUR"');

        $this->apiClient->method('get')
            ->willThrowException(new UnableToDecodeAPIResponseException('Failed to decode BIN API response'));

        $this->expectException(UnableToDecodeAPIResponseException::class);
        $this->transactionProcessor->processTransactions($filePath);

        unlink($filePath);
    }
}
