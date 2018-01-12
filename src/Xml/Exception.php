<?php

namespace Kamazee\PhpqaReportTool\Xml;

use DOMElement;
use Kamazee\PhpqaReportTool\Exception as BaseException;
use Kamazee\PhpqaReportTool\Filesystem\FileException;
use LibXMLError;

class Exception extends BaseException
{
    const MISSING_ATTRIBUTE_CODE = 1;
    const CANT_READ_FILE_CODE = 2;
    const CANT_LOAD_XML_CODE = 3;

    public static function missingAttribute($name, DOMElement $element)
    {
        return new self(
            "Missing attribute '$name' is requested " .
            "for '{$element->tagName}' element",
            self::MISSING_ATTRIBUTE_CODE
        );
    }

    public static function cantReadFile(FileException $e)
    {
        return new self(
            "Failed to read XML file: $e->message",
            self::CANT_READ_FILE_CODE,
            $e
        );
    }

    /**
     * @param string $filename
     * @param LibXMLError[] $errors
     *
     * @return Exception
     */
    public static function cantLoadXmlFromFile(string $filename, array $errors)
    {
        $message = "Can't load XML from $filename";

        if (0 === count($errors)) {
            $additionalMessage = 'Unknown error';
        } else {
            $error = $errors[0];
            $additionalMessage = "Line {$error->line}: {$error->message}";
        }

        return new self(
            "$message ($additionalMessage)",
            self::CANT_LOAD_XML_CODE
        );
    }
}
