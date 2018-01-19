<?php

namespace Kamazee\PhpqaReportTool\Checkstyle;

use Kamazee\PhpqaReportTool\Diff\Diff;
use Kamazee\PhpqaReportTool\Filesystem\FileException;
use Kamazee\PhpqaReportTool\Xml\Exception as XmlException;
use Kamazee\PhpqaReportTool\Xml\Loader;
use Kamazee\PhpqaReportTool\Xml\Node;
use const DIRECTORY_SEPARATOR;
use function strlen;
use function strpos;
use function substr;

class Filter
{
    private $diff;
    private $loader;

    public function __construct(Loader $loader, Diff $diff)
    {
        $this->diff = $diff;
        $this->loader = $loader;
    }

    public function filter($inputFilename, $outputFilename, $basePath = null)
    {
        if (null !== $basePath) {
            $basePath = rtrim($basePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        }

        try {
            $xml = $this->loader->loadFromFile($inputFilename);
        } catch (XmlException $e) {
            throw FilterException::cantReadCheckstyle($e, $inputFilename);
        }

        $checkstyle = new Node($xml->documentElement);
        $filesInCheckstyle = 0;
        $filesFilteredOut = 0;
        foreach ($checkstyle->getChildren() as $file) {
            if (!$file->isTagNameEqualTo('file')) {
                continue;
            }

            ++$filesInCheckstyle;
            $filename = $file->getAttribute('name');
            if (null !== $basePath && 0 === strpos($filename, $basePath)) {
                $filename = substr($filename, strlen($basePath));
                $file->setAttribute('name', $filename);
            }

            $errorsInFile = 0;
            $errorsFilteredOut = 0;
            foreach ($file->getChildren() as $error) {
                if (!$error->isTagNameEqualTo('error')) {
                    continue;
                }

                ++$errorsInFile;
                $line = (int) $error->getAttribute('line');
                if (!$this->diff->isNewCode($filename, $line)) {
                    ++$errorsFilteredOut;
                    $file->remove($error);
                }
            }

            if ($errorsInFile === $errorsFilteredOut) {
                $checkstyle->remove($file);
                ++$filesFilteredOut;
            }
        }

        if ($filesInCheckstyle === $filesFilteredOut) {
            $checkstyle->trim();
        }

        $xml->save($outputFilename);
    }
}
