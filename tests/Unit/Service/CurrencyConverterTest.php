<?php declare(strict_types=1);

namespace Unit\Service;

use App\ApiClient\ApiClientInterface;
use App\Exception\UnableToDecodeAPIResponseException;
use App\Exception\UnsupportedCurrencyException;
use App\Service\CurrencyConverter;
use JsonException;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class CurrencyConverterTest extends TestCase
{
    private ApiClientInterface $apiClientMock;
    private CurrencyConverter $currencyConverter;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->apiClientMock = $this->createMock(ApiClientInterface::class);
        $this->currencyConverter = new CurrencyConverter($this->apiClientMock, 'https://example.com/api', 'fake_api_key');
    }

    /**
     * @throws UnsupportedCurrencyException
     * @throws UnableToDecodeAPIResponseException
     * @throws JsonException
     */
    public function testConvertSameCurrency(): void
    {
        $this->apiClientMock->expects($this->once())
            ->method('get')
            ->willReturn(json_encode(['rates' => ['EUR' => 1.0]], JSON_THROW_ON_ERROR));

        $amount = $this->currencyConverter->convert(100, 'EUR', 'EUR');
        $this->assertEquals(100, $amount);
    }

    /**
     * @throws UnsupportedCurrencyException
     * @throws UnableToDecodeAPIResponseException
     * @throws JsonException
     */
    public function testConvertFromEuro(): void
    {
        $this->apiClientMock->expects($this->once())
            ->method('get')
            ->willReturn(json_encode(['rates' => ['USD' => 1.2]], JSON_THROW_ON_ERROR));

        $amount = $this->currencyConverter->convert(100, 'EUR', 'USD');
        $this->assertEquals(120, $amount);
    }

    /**
     * @throws UnsupportedCurrencyException
     * @throws UnableToDecodeAPIResponseException
     * @throws JsonException
     */
    public function testConvertToEuro(): void
    {
        $this->apiClientMock->expects($this->once())
            ->method('get')
            ->willReturn(json_encode(['rates' => ['USD' => 1.2]], JSON_THROW_ON_ERROR));

        $amount = $this->currencyConverter->convert(120, 'USD', 'EUR');
        $this->assertEquals(100, $amount);
    }

    /**
     * @throws UnsupportedCurrencyException
     * @throws UnableToDecodeAPIResponseException
     * @throws JsonException
     */
    public function testConvertBetweenCurrencies(): void
    {
        $this->apiClientMock->expects($this->once())
            ->method('get')
            ->willReturn(json_encode(['rates' => ['USD' => 1.2, 'GBP' => 0.9]], JSON_THROW_ON_ERROR));

        $amount = $this->currencyConverter->convert(120, 'USD', 'GBP');
        $this->assertEquals(90, $amount);
    }

    /**
     * @throws UnsupportedCurrencyException
     * @throws UnableToDecodeAPIResponseException
     * @throws JsonException
     */
    public function testUnsupportedCurrencyException(): void
    {
        $this->apiClientMock->expects($this->once())
            ->method('get')
            ->willReturn(json_encode(['rates' => ['USD' => 1.2]], JSON_THROW_ON_ERROR));

        $this->expectException(UnsupportedCurrencyException::class);
        $this->expectExceptionMessage('Currency is not supported: GBP');

        $this->currencyConverter->convert(100, 'GBP', 'EUR');
    }

    /**
     * @throws UnsupportedCurrencyException
     * @throws UnableToDecodeAPIResponseException
     * @throws JsonException
     */
    public function testCeilByCents(): void
    {
        $this->apiClientMock->expects($this->once())
            ->method('get')
            ->willReturn(json_encode(['rates' => ['USD' => 1.2]], JSON_THROW_ON_ERROR));

        $amount = $this->currencyConverter->convert(123.456, 'USD', 'EUR');
        $this->assertEquals(102.89, $amount);
    }
}
