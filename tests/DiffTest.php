<?php

namespace Kamazee\PrFilter;

use DI\ContainerBuilder;
use DirectoryIterator;
use Exception;
use Kamazee\PrFilter\Diff\Factory;
use PHPUnit\Framework\TestCase;
use SplFileInfo;
use stdClass;
use const DIRECTORY_SEPARATOR;
use function file_exists;
use function file_get_contents;
use function implode;
use function is_bool;
use function is_int;
use function is_string;
use function json_decode;
use function property_exists;

class DiffTest extends TestCase
{
    const DATA_DIR = __DIR__ . DIRECTORY_SEPARATOR . 'DiffTestData';

    /**
     * @param string $directoryName
     * @throws Exception
     *
     * @dataProvider getCases
     */
    public function test($directoryName)
    {
        $directory = new SplFileInfo($directoryName);
        $base = $directory->getPathname();
        $diffFilename = implode(DIRECTORY_SEPARATOR, [$base, 'diff']);
        if (!file_exists($diffFilename)) {
            throw self::noDiff($directory);
        }

        $expectationsFilename = implode(
            DIRECTORY_SEPARATOR,
            [$base, 'expectations.json']
        );

        if (!file_exists($expectationsFilename)) {
            throw self::noExpectation($directory);
        }

        $diff = self::getFactory()->git($diffFilename);

        $expectations = json_decode(file_get_contents($expectationsFilename));
        self::validateExpectations($directory->getFilename(), $expectations);

        foreach ($expectations->lines as $expectation) {
            self::assertEquals(
                $expectation->result,
                $diff->isNewCode($expectation->file, $expectation->line),
                "{$expectation->file}:{$expectation->line}"
            );
        }
    }

    public static function getCases()
    {
        $dir = new DirectoryIterator(self::DATA_DIR);
        foreach ($dir as $d) {
            if ($d->isDot()) {
                continue;
            }

            if (!$d->isDir()) {
                continue;
            }

            yield $d->getFilename() => [$d->getPathname()];
        }
    }

    /**
     * @param string $filename
     * @param mixed $expectations
     *
     * @throws Exception
     */
    private static function validateExpectations($filename, $expectations)
    {
        if (!$expectations instanceof stdClass) {
            throw self::malformedExpectationsObject(
                "$filename: structure in expectations file is not an object"
            );
        }

        if (!property_exists($expectations, 'files')) {
            throw self::malformedExpectationsObject(
                "$filename: 'files' property is not set"
            );
        }

        if (!is_array($expectations->files)) {
            throw self::malformedExpectationsObject(
                "$filename: 'files' property must contain an array"
            );
        }

        if (!property_exists($expectations, 'lines')) {
            throw self::malformedExpectationsObject(
                "$filename: 'lines' property is not set"
            );
        }

        if (!is_array($expectations->lines)) {
            throw self::malformedExpectationsObject(
                "$filename: 'lines' property must contain an array"
            );
        }

        self::validateLineExpectations($filename, $expectations->lines);
    }

    /**
     * @param string $filename
     * @param array $lines
     *
     * @throws Exception
     */
    private static function validateLineExpectations($filename, array $lines)
    {
        foreach ($lines as $key => $line) {
            if (!$line instanceof stdClass) {
                throw self::malformedExpectationsObject(
                    "$filename lines[$key]: value must be an object"
                );
            }

            self::validateLineExpectation($filename, $key, $line);
        }
    }

    /**
     * @param int $index
     * @param stdClass $line
     *
     * @throws Exception
     */
    private static function validateLineExpectation($filename, $index, stdClass $line)
    {
        if (!property_exists($line, 'file')) {
            throw self::malformedExpectationsObject(
                "$filename: lines[$index]: file property must be set"
            );
        }

        if (!is_string($line->file)) {
            throw self::malformedExpectationsObject(
                "$filename: lines[$index]: file must be a string"
            );
        }

        if (!property_exists($line, 'line')) {
            throw self::malformedExpectationsObject(
                "$filename: lines[$index]: line property must be set"
            );
        }

        if (!is_int($line->line)) {
            throw self::malformedExpectationsObject(
                "$filename: lines[$index]: line property must be an int"
            );
        }

        if (!property_exists($line, 'result')) {
            throw self::malformedExpectationsObject(
                "$filename: lines[$index]: result property must be set"
            );
        }

        if (!is_bool($line->result)) {
            throw self::malformedExpectationsObject(
                "$filename: lines[$index]: result property must be a bool"
            );
        }
    }

    /**
     * @return Factory
     *
     * @throws Exception
     */
    private static function getFactory()
    {
        try {
            return ContainerBuilder::buildDevContainer()->get(
                Factory::class
            );
        } catch (Exception $e) {
            throw self::cantInstantiateDiffFactory($e);
        }
    }

    public static function noDiff(SplFileInfo $d)
    {
        return new Exception("'diff' file is missing in {$d->getPathname()}");
    }

    public static function noExpectation(SplFileInfo $d)
    {
        return new Exception(
            "expectations.json file is missing in {$d->getPathname()}"
        );
    }

    public static function cantInstantiateDiffFactory(Exception $e)
    {
        return new Exception(
            "Can't instantiate DiffFactory: {$e->getMessage()}",
            0,
            $e
        );
    }

    public static function malformedExpectationsObject($message)
    {
        return new Exception("Malformed expectations object. $message");
    }
}
