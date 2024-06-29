<?php declare(strict_types=1);

namespace App\Exception;

use Exception;

abstract class AbstractException extends Exception
{
    private string $errorCode;
    private int $statusCode;

    public function __construct(
        string $message,
        string $errorCode,
        ?\Throwable $previous = null,
        int $statusCode = 500
    ) {
        parent::__construct($message, 0, $previous);
        $this->errorCode = $errorCode;
        $this->statusCode = $statusCode;
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
