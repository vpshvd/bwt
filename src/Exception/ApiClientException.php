<?php declare(strict_types=1);

namespace App\Exception;

final class ApiClientException extends AbstractException
{
    private const string ERROR_CODE = 'ERROR_FETCHING_DATA';

    public function __construct(string $errorMessage, int $statusCode, ?\Throwable $previous = null)
    {
        parent::__construct(
            message: $errorMessage,
            errorCode: self::ERROR_CODE,
            previous: $previous,
            statusCode: $statusCode,
        );
    }
}
