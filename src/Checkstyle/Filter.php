<?php

namespace Kamazee\PrFilter\Checkstyle;

use Kamazee\PrFilter\Diff\Diff;
use Kamazee\PrFilter\Filesystem\FileException;
use Kamazee\PrFilter\Xml\Exception as XmlException;
use Kamazee\PrFilter\Xml\Loader;
use Kamazee\PrFilter\Xml\Node;
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
