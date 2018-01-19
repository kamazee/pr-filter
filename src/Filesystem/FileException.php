<?php

namespace Kamazee\PrFilter\Filesystem;

use Kamazee\PrFilter\Exception;
use function sprintf;

class FileException extends Exception
{
    const FILE_NOT_EXISTS_CODE = 1;
    const FILE_NOT_EXISTS = 'File %s does not exist';

    const NOT_A_FILE_CODE = 2;
    const NOT_A_FILE = '%s is not a file';

    const NOT_READABLE_CODE = 3;
    const NOT_READABLE = '%s is not readable';

    const READING_FAILED_CODE = 4;
    const READING_FAILED = 'Reading %s failed: %s';

    public static function fileNotExists(string $filename): self
    {
        return new self(
            sprintf(self::FILE_NOT_EXISTS, $filename),
            self::FILE_NOT_EXISTS_CODE
        );
    }

    public static function notAFile(string $filename): self
    {
        return new self(
            sprintf(self::NOT_A_FILE, $filename),
            self::NOT_A_FILE_CODE
        );
    }

    public static function fileNotReadable(string $filename): self
    {
        return new self(
            sprintf(self::NOT_READABLE, $filename),
            self::NOT_READABLE_CODE
        );
    }

    public static function readingFailed($filename, array $error = null): self
    {
        $error = (array) $error + ['message' => 'Unknown error'];
        return new self(
            sprintf(self::READING_FAILED, $filename, $error['message'])
        );
    }
}
