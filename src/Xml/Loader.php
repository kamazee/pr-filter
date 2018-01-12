<?php

namespace Kamazee\PhpqaReportTool\Xml;

use DOMDocument;
use Kamazee\PhpqaReportTool\Filesystem\FileException;
use Kamazee\PhpqaReportTool\Filesystem\FileFactory;
use const LIBXML_NONET;
use function libxml_get_errors;
use function libxml_use_internal_errors;

class Loader
{
    private $fileFactory;

    public function __construct(FileFactory $fileFactory)
    {
        $this->fileFactory = $fileFactory;
    }

    /**
     * @param string $filename
     * @return DOMDocument
     * @throws Exception
     */
    public function loadFromFile(string $filename)
    {
        try {
            $this->fileFactory->file($filename)->assertFileIsReadable();
        } catch (FileException $e) {
            throw Exception::cantReadFile($e);
        }

        $xml = new DOMDocument();
        $previousState = libxml_use_internal_errors(true);
        if (!$xml->load($filename, LIBXML_NONET)) {
            libxml_use_internal_errors($previousState);
            throw Exception::cantLoadXmlFromFile(
                $filename,
                libxml_get_errors()
            );
        }

        libxml_use_internal_errors($previousState);

        return $xml;
    }
}
