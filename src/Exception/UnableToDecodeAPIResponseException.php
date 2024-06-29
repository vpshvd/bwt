<?php declare(strict_types=1);

namespace App\Exception;

class UnableToDecodeAPIResponseException extends AbstractException
{
    private const string ERROR_CODE = 'UNABLE_TO_DECODE_API_RESPONSE CURRENCY';

    public function __construct(string $message, ?\Throwable $previous = null)
    {
        parent::__construct($message, self::ERROR_CODE, $previous);
    }
}
