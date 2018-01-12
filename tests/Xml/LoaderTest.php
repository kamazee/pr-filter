<?php declare(strict_types=1);

namespace Kamazee\PhpqaReportTool\Xml;

use Kamazee\PhpqaReportTool\Filesystem\File;
use Kamazee\PhpqaReportTool\Filesystem\FileException;
use Kamazee\PhpqaReportTool\Filesystem\FileFactory;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LoaderTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testThrowsOnInvalidXmlInFile()
    {
        $vfs = vfsStream::setup();
        $brokenXml = '<?';
        $file = vfsStream::newFile('test.xml')
            ->at($vfs)
            ->setContent($brokenXml)
            ->url();

        /** @var MockObject|File $fileMock */
        $fileMock = $this->getMockBuilder(File::class)
            ->disableOriginalConstructor()
            ->setMethods(['assertFileIsReadable'])
            ->getMock();

        $fileMock->expects($this->once())
            ->method('assertFileIsReadable')
            ->willReturn(null);

        /** @var MockObject|FileFactory $fileFactoryMock */
        $fileFactoryMock = $this->getMockBuilder(FileFactory::class)
            ->setMethods(['file'])
            ->getMock();

        $fileFactoryMock->expects($this->once())
            ->method('file')
            ->with($file)
            ->willReturn($fileMock);


        $this->expectException(Exception::class);
        $this->expectExceptionCode(Exception::CANT_LOAD_XML_CODE);
        (new Loader($fileFactoryMock))
            ->loadFromFile($file);
    }

    /**
     * @throws Exception
     */
    public function testThrowsOnNotReadableFile()
    {
        $file = 'test.xml';

        /** @var MockObject|File $fileMock */
        $fileMock = $this->getMockBuilder(File::class)
            ->disableOriginalConstructor()
            ->setMethods(['assertFileIsReadable'])
            ->getMock();

        $fileMock->expects($this->once())
            ->method('assertFileIsReadable')
            ->willThrowException(FileException::readingFailed($file, []));

        /** @var MockObject|FileFactory $fileFactoryMock */
        $fileFactoryMock = $this->getMockBuilder(FileFactory::class)
            ->setMethods(['file'])
            ->getMock();

        $fileFactoryMock->expects($this->once())
            ->method('file')
            ->with($file)
            ->willReturn($fileMock);


        $this->expectException(Exception::class);
        $this->expectExceptionCode(Exception::CANT_READ_FILE_CODE);
        (new Loader($fileFactoryMock))
            ->loadFromFile($file);
    }
}
