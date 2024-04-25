<?php

namespace Kamazee\PrFilter\Filesystem;

use PHPUnit\Framework\TestCase;
use function error_clear_last;
use function error_get_last;
use function preg_quote;
use function sprintf;
use function trigger_error;

class FileExceptionTest extends TestCase
{
    public function testFileReadingErrorWithPhpError()
    {
        $filename = 'test.txt';
        $errorText = 'File is unreadable';
        @trigger_error($errorText);
        $error = error_get_last();
        error_clear_last();
        $exception = FileException::readingFailed($filename, $error);

        $this->assertMatchesRegularExpression(
            sprintf('#\b%s\b#', preg_quote($filename, '#')),
            $exception->getMessage()
        );

        $this->assertMatchesRegularExpression(
            sprintf('#\b%s\b#', preg_quote($errorText)),
            $exception->getMessage()
        );
    }

    /**
     * When no php error occurs, `error_get_last()` returns null
     *
     * @dataProvider emptyErrorsDataProvider
     */
    public function testFileReadingErrorWithNoPhpError($emptyError)
    {
        $filename = 'test.txt';
        $errorText = 'Unknown error';
        $exception = FileException::readingFailed($filename, $emptyError);

        $this->assertMatchesRegularExpression(
            sprintf('#\b%s\b#', preg_quote($filename, '#')),
            $exception->getMessage()
        );

        $this->assertMatchesRegularExpression(
            sprintf('#\b%s\b#', preg_quote($errorText)),
            $exception->getMessage()
        );
    }

    public function emptyErrorsDataProvider()
    {
        return [
            'null' => [null],
            'empty array' => [[]],
            'array without message' => [['code' => 0]],
        ];
    }
}
