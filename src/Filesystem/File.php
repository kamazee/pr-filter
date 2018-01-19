<?php

namespace Kamazee\PrFilter\Filesystem;

use function error_get_last;
use function file_exists;
use function file_get_contents;
use function is_readable;

/**
 * Simple class that implements a frequent operation: reading a file with
 * error handling (intention to not copy/paste it and have nice error messages
 * is the reason why it's in a separate utility).
 *
 * It doesn't aim for atomicity of anything (file could get removed, chmod'ed,
 * chown'ed between checks -- working that around isn't the goal here as it's
 * complex and not likely enough to happen significantly often)
 */
class File
{
    private $filename;

    public function __construct(string $filename)
    {
        $this->filename = $filename;
    }

    /**
     * @throws FileException
     */
    public function assertFileIsReadable(): void
    {
        $this->assertExists();
        $this->assertFile();
        $this->assertReadable();
    }

    /**
     * @return string
     *
     * @throws FileException
     */
    public function read(): string
    {
        $this->assertFileIsReadable();
        $content = @file_get_contents($this->filename);
        if (false === $content) {
            throw FileException::readingFailed(
                $this->filename,
                error_get_last()
            );
        }

        return $content;
    }

    /**
     * @throws FileException
     */
    private function assertExists(): void
    {
        if (file_exists($this->filename)) {
            return;
        }

        throw FileException::fileNotExists($this->filename);
    }

    /**
     * @throws FileException
     */
    private function assertFile(): void
    {
        if (is_file($this->filename)) {
            return;
        }

        throw FileException::notAFile($this->filename);
    }

    /**
     * @throws FileException
     */
    private function assertReadable(): void
    {
        if (is_readable($this->filename)) {
            return;
        }

        throw FileException::fileNotReadable($this->filename);
    }
}
