<?php

namespace Kamazee\PhpqaReportTool\Checkstyle;

use Kamazee\PhpqaReportTool\Exception;
use Kamazee\PhpqaReportTool\Xml\Exception as XmlException;

class FilterException extends Exception
{
    public static function cantReadCheckstyle(XmlException $e, string $filename)
    {
        return new self("Can't load XML from $filename", null, $e);
    }
}
