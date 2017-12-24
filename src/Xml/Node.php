<?php

namespace Kamazee\PhpqaReportTool\Xml;

use DOMElement;
use DOMNode;
use DOMText;
use Generator;

class Node
{
    private $element;
    private $trailingWhitespace;

    public function __construct(DOMElement $element, DOMText $whitespace = null)
    {
        $this->element = $element;
        $this->trailingWhitespace = $whitespace;
    }

    /**
     * @return Generator|Node[]
     */
    public function getChildren()
    {
        $whitespace = null;
        foreach ($this->getAllChildrenBackwards() as $child) {
            if (self::isWhitespaceNode($child)) {
                $whitespace = $child;
                continue;
            }

            if (!$child instanceof DOMElement) {
                $whitespace = null;
                continue;
            }

            yield new Node($child, $whitespace);
        }
    }

    public function isTagNameEqualTo($expectedTagName)
    {
        return $expectedTagName === $this->element->tagName;
    }

    public function remove(Node $node)
    {
        $this->element->removeChild($node->element);
        if (null !== $node->trailingWhitespace) {
            $this->element->removeChild($node->trailingWhitespace);
        }
    }

    public function trim()
    {
        $this->rtrim();
        $this->ltrim();
    }

    public function rtrim()
    {
        foreach ($this->getAllChildrenBackwards() as $child) {
            if (!self::isWhitespaceNode($child)) {
                break;
            }

            $this->element->removeChild($child);
        }
    }

    public function ltrim()
    {
        $remove = [];
        foreach ($this->element->childNodes as $child) {
            if (!self::isWhitespaceNode($child)) {
                break;
            }

            $remove[] = $child;
        }

        foreach ($remove as $node) {
            $this->element->removeChild($node);
        }
    }

    public function isEmpty()
    {
        return 0 === $this->element->childNodes->length;
    }

    /**
     * @param $name
     * @return string
     * @throws Exception
     */
    public function getAttribute($name)
    {
        if (!$this->element->hasAttribute($name)) {
            throw Exception::missingAttribute($name, $this->element);
        }

        return $this->element->getAttribute($name);
    }

    /**
     * Returns children in backward order to allow easy removals
     */
    private function getAllChildrenBackwards()
    {
        $children = $this->element->childNodes;
        $childrenCount = $children->length;
        for ($i = $childrenCount - 1; $i >= 0; $i--) {
            yield $children->item($i);
        }
    }

    private static function isWhitespaceNode(DOMNode $node)
    {
        if (!$node instanceof DOMText) {
            return false;
        }

        return 0 === strlen(trim($node->textContent));
    }
}
