<?php

namespace Kamazee\PrFilter\Phpcs;

use DOMDocument;
use DOMElement;
use Kamazee\PrFilter\Xml\Node;

class Config
{
    /**
     * @var DOMDocument
     */
    private $xml;

    /**
     * @param DOMDocument $xml
     */
    public function __construct(DOMDocument $xml)
    {
        $this->xml = $xml;
    }

    /**
     * @param array $filenames
     */
    public function setAnalyzedFiles(array $filenames): void
    {
        $root = new Node($this->xml->documentElement);
        foreach ($root->getChildren() as $child) {
            if (!$child->isTagNameEqualTo('file')) {
                continue;
            }

            $root->remove($child);
        }

        foreach ($filenames as $filename) {
            $this->xml->documentElement->appendChild(
                new DOMElement('file', $filename)
            );
        }
    }

    public function write($filename)
    {
        $this->xml->save($filename);
    }
}
