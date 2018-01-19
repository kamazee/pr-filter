<?php

namespace Kamazee\PrFilter\Phan;

use Kamazee\PrFilter\Exception;
use Kamazee\PrFilter\Filesystem\FileException;

class ConfigException extends Exception
{
    const WRITING_CONFIG_FAILED_CODE = 1;
    const CANT_READ_CONFIG_CODE = 2;
    const CONFIG_IS_NOT_AN_ARRAY_CODE = 3;
    const OUTPUT_FILE_NOT_SPECIFIED_CODE = 4;

    public static function writingConfigFailed(string $filename, array $error)
    {
        return new self(
            "Failed to write phan config to $filename: {$error['message']}",
            self::WRITING_CONFIG_FAILED_CODE
        );
    }

    public static function cantReadConfig(FileException $e)
    {
        return new self(
            "Failed to read phan config: $e->message",
            self::CANT_READ_CONFIG_CODE,
            $e
        );
    }

    public static function configIsNotAnArray(string $filename)
    {
        return new self(
            "Phan config at $filename is not an array",
            self::CONFIG_IS_NOT_AN_ARRAY_CODE
        );
    }
}
