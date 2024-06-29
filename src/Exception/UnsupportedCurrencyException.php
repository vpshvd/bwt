<?php declare(strict_types=1);

namespace App\Exception;

final class UnsupportedCurrencyException extends AbstractException
{
    private const string ERROR_CODE = 'UNSUPPORTED CURRENCY';
    private const int STATUS_CODE = 422;


    public function __construct(string $currency)
    {
        parent::__construct('Currency is not supported: ' . $currency, self::ERROR_CODE, null, self::STATUS_CODE);
    }
}
