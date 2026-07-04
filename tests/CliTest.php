<?php

namespace Kamazee\PrFilter;

use PHPUnit\Framework\TestCase;
use function fclose;
use function is_resource;
use function proc_close;
use function proc_open;
use function stream_get_contents;
use function sys_get_temp_dir;
use function tempnam;
use function unlink;
use const DIRECTORY_SEPARATOR;
use const PHP_BINARY;

class CliTest extends TestCase
{
    const DATA_DIR = __DIR__ . '/CliTestData/filter-checkstyle';

    public function testFilterCheckstyleCommandSucceedsWithoutOutput()
    {
        $output = tempnam(sys_get_temp_dir(), 'prf-checkstyle-');
        self::assertIsString($output);

        try {
            $result = $this->runCommand([
                PHP_BINARY,
                __DIR__ . '/../bin/prf',
                'filter-checkstyle',
                self::DATA_DIR . DIRECTORY_SEPARATOR . 'diff',
                self::DATA_DIR . DIRECTORY_SEPARATOR . 'checkstyle.xml',
                $output,
            ]);

            self::assertSame('', $result['stdout']);
            self::assertSame('', $result['stderr']);
            self::assertSame(0, $result['exit_code']);
            self::assertXmlFileEqualsXmlFile(
                self::DATA_DIR . DIRECTORY_SEPARATOR . 'checkstyle-expected.xml',
                $output
            );
        } finally {
            if (false !== $output) {
                unlink($output);
            }
        }
    }

    private function runCommand(array $command)
    {
        $process = proc_open(
            implode(' ', array_map('escapeshellarg', $command)),
            [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ],
            $pipes,
            __DIR__ . '/..'
        );

        self::assertTrue(is_resource($process), 'CLI process must start');

        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);

        return [
            'stdout' => $stdout,
            'stderr' => $stderr,
            'exit_code' => proc_close($process),
        ];
    }
}
