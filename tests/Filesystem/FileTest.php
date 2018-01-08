<?php

namespace Kamazee\PhpqaReportTool\Filesystem;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use function sprintf;

class FileTest extends TestCase
{
    /** @var vfsStreamDirectory */
    private $vfs;

    public function setUp()
    {
        $this->vfs = vfsStream::setup();
    }

    public function testThrowsOnNonExistingFile()
    {
        $file = $this->vfs->url() . '/i_do_not_exist';
        $expectedException = FileException::fileNotExists($file);

        $this->tryAssertAndRead(new File($file), $expectedException);
    }

    public function testThrowsOnNonFile()
    {
        $file = $this->vfs->url();
        $expectedException = FileException::notAFile($file);

        $this->tryAssertAndRead(new File($file), $expectedException);
    }

    public function testThrowsOnNotReadableFile()
    {
        $name = 'existing_file';
        $file = vfsStream::newFile($name)->at($this->vfs)
            ->setContent('')
            ->chmod(0200)
            ->url();

        $expectedException = FileException::fileNotReadable($file);

        $this->tryAssertAndRead(new File($file), $expectedException);
    }

    /**
     * @throws FileException
     */
    public function testReadReturnsContent()
    {
        $name = 'existing_file';
        $expectedContent = 'expected_content';
        $file = vfsStream::newFile($name)->at($this->vfs)
            ->setContent($expectedContent);

        $this->assertEquals(
            $expectedContent,
            (new File($file->url()))->read()
        );
    }

    private function tryAssertAndRead(File $file, FileException $expectedException)
    {
        try {
            $file->read();
            $this->fail("Expected exception wasn't thrown when reading");
        } catch (FileException $actualException) {
            $this->assertEquals($expectedException, $actualException);
        }

        try {
            $file->assertFileIsReadable();
            $this->fail(
                "Expected exception wasn't thrown when asserting readability"
            );
        } catch (FileException $actualException) {
            $this->assertEquals($expectedException, $actualException);
        }
    }

    /**
     * @throws FileException
     */
    public function testThrowsWhenReadFails()
    {
        $name = 'existing_file';
        $expectedContent = 'expected_content';

        // There will be not enough permissions...
        $file = vfsStream::newFile($name)->at($this->vfs)
            ->setContent($expectedContent)
            ->chmod(0200);

        $this->expectException(FileException::class);
        $this->expectExceptionMessageRegExp(
            sprintf('#Reading %s failed.*file_get_contents.*#', $file->url())
        );

        /** @var File|MockObject $mock */
        $mock = $this->getMockBuilder(File::class)
            ->setMethods(['assertFileIsReadable'])
            ->setConstructorArgs([$file->url()])
            ->getMock();

        // ... but asserts will pass; we pretend
        // that permission are changed after asserts have been made
        $mock->method('assertFileIsReadable')
            ->willReturn(null);

        $mock->read();
    }
}
