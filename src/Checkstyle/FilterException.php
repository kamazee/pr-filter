<?php

namespace Kamazee\PrFilter\Checkstyle;

use Kamazee\PrFilter\Exception;
use Kamazee\PrFilter\Xml\Exception as XmlException;

class FilterException extends Exception
{
    public static function cantReadCheckstyle(XmlException $e, string $filename)
    {
        return new self("Can't load XML from $filename", 0, $e);
    }
}
