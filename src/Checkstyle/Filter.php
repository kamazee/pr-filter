<?php

namespace Kamazee\PhpqaReportTool\Checkstyle;

use DOMDocument;
use Kamazee\PhpqaReportTool\Diff\Diff;
use Kamazee\PhpqaReportTool\Xml\Node;
use const DIRECTORY_SEPARATOR;
use function strlen;
use function strpos;
use function substr;

class Filter
{
    private $diff;

    public function __construct(Diff $diff)
    {
        $this->diff = $diff;
    }

    public function filter($inputFilename, $outputFilename, $basePath = null)
    {
        if (null !== $basePath) {
            $basePath = rtrim($basePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        }

        $xml = new DOMDocument();
        $xml->load($inputFilename);
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
