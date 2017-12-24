<?php

namespace Kamazee\PhpqaReportTool\Xml;

use DOMElement;
use Kamazee\PhpqaReportTool\Exception as BaseException;

class Exception extends BaseException
{
    public static function missingAttribute($name, DOMElement $element)
    {
        return new self(
            "Missing attribute '$name' is requested " .
            "for '{$element->tagName}' element"
        );
    }
}
