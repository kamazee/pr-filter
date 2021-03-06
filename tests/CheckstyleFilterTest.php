<?php

namespace Kamazee\PrFilter;

use function array_key_exists;
use DirectoryIterator;
use Kamazee\PrFilter\Checkstyle\Filter;
use Kamazee\PrFilter\Checkstyle\FilterException;
use Kamazee\PrFilter\Diff\Diff;
use Kamazee\PrFilter\Filesystem\FileFactory;
use Kamazee\PrFilter\Xml\Loader;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionProperty;
use const DIRECTORY_SEPARATOR;
use function file_get_contents;
use function json_decode;
use SplFileInfo;
use Kamazee\PrFilter\Xml\Exception as XmlException;

class CheckstyleFilterTest extends TestCase
{
    const DATA_DIR = __DIR__ . '/CheckstyleFilterTestData';

    /**
     * @dataProvider getCases
     * @param string $directoryName
     * @throws Checkstyle\FilterException
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
        ) + ['base_path' => null];
        $diff = $this->getDiffMock($data['new_code']);
        $checkstyle = new Filter(new Loader(new FileFactory()), $diff);
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

    public function testThrowsWhenCantLoadFile()
    {
        $file = 'test.xml';

        /** @var Loader|MockObject $loader */
        $loader = $this->getMockBuilder(Loader::class)
            ->disableOriginalConstructor()
            ->setMethods(['loadFromFile'])
            ->getMock();

        $loader->expects($this->once())
            ->method('loadFromFile')
            ->with($file)
            ->willThrowException(
                FilterException::cantReadCheckstyle(
                    XmlException::cantLoadXmlFromFile($file, []),
                    $file
                )
            );

        /** @var Diff|MockObject $diff */
        $diff = $this->getMockBuilder(Diff::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->expectException(FilterException::class);

        (new Filter($loader, $diff))->filter($file, $file);
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
