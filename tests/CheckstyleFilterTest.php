<?php

namespace Kamazee\PhpqaReportTool;

use function array_key_exists;
use DirectoryIterator;
use Kamazee\PhpqaReportTool\Checkstyle\Filter;
use Kamazee\PhpqaReportTool\Diff\Diff;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionProperty;
use const DIRECTORY_SEPARATOR;
use function file_get_contents;
use function json_decode;
use SplFileInfo;

class CheckstyleFilterTest extends TestCase
{
    const DATA_DIR = __DIR__ . '/CheckstyleFilterTestData';

    /**
     * @dataProvider getCases
     * @param string $directoryName
     */
    public function test($directoryName)
    {
        $directory = new SplFileInfo($directoryName);
        $vfs = vfsStream::setup();
        $base = $directory->getPathname();
        $data = json_decode(
            file_get_contents(
                $base . DIRECTORY_SEPARATOR . 'data.json'
            ),
            true
        );
        $diff = $this->getDiffMock($data['new_code']);
        $checkstyle = new Filter($diff);
        $output = $vfs->url() . '/checkstyle-filtered.xml';
        $checkstyle->filter(
            $base . DIRECTORY_SEPARATOR . 'checkstyle.xml',
            $output,
            $data['base_path']
        );

        self::assertFileExists($output);

        $expected = $base . DIRECTORY_SEPARATOR . 'checkstyle-expected.xml';
        self::assertXmlFileEqualsXmlFile(
            $expected,
            $output,
            "{$directory->getFilename()} XML structure must match the expected"
        );

        if (array_key_exists('compare_filtered_xml_as_string', $data) &&
            true === $data['compare_filtered_xml_as_string']
        ) {
            self::assertFileEquals(
                $expected,
                $output,
                "{$directory->getFilename()} file contents must match the expected"
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
     * @param array $files
     *
     * @return Diff
     */
    private function getDiffMock(array $files)
    {
        /** @var Diff $mock */
        $mock = (new ReflectionClass(Diff::class))
            ->newInstanceWithoutConstructor();

        $property = new ReflectionProperty(Diff::class, 'files');
        $property->setAccessible(true);
        $property->setValue($mock, $files);

        return $mock;
    }
}
