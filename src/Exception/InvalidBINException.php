<?php declare(strict_types=1);

namespace App\Exception;

final class InvalidBINException extends AbstractException
{
    private const string ERROR_CODE = 'INVALID_BIN_DATA';
    private const int STATUS_CODE = 400;

    public function __construct(string $bin, string $message = 'Invalid BIN data')
    {
        $detailedMessage = sprintf('%s: %s', $message, $bin);
        parent::__construct($detailedMessage, self::ERROR_CODE, null, self::STATUS_CODE);
    }
}
