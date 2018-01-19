<?php

namespace Kamazee\PrFilter\Xml;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use function str_repeat;

class NodeTest extends TestCase
{
    public function testIsEmpty()
    {
        $node = self::getRootNodeForXml('<checkstyle></checkstyle>');
        self::assertTrue($node->isEmpty());
    }

    public function testIsNotEmpty()
    {
        $node = self::getRootNodeForXml('<checkstyle><error/></checkstyle>');
        self::assertFalse($node->isEmpty());
    }

    public function testWhitespaceIsNotEmpty()
    {
        $node = self::getRootNodeForXml("<checkstyle>\n</checkstyle>");
        self::assertFalse($node->isEmpty());
    }


    public function testLtrim()
    {
        for ($newlinesCount = 1; $newlinesCount <= 2; $newlinesCount++) {
            $newlines = str_repeat("\n", $newlinesCount);
            $dom = self::getDom("<checkstyle>$newlines<error/>\n</checkstyle>");
            $node = self::getRootNodeForDom($dom);
            $node->ltrim();

            $expected = self::getCompleteXml(
                "<checkstyle><error/>\n</checkstyle>"
            );

            self::assertEquals(
                $expected,
                $dom->saveXML()
            );
        }
    }

    public function testRtrim()
    {
        for ($newlinesCount = 1; $newlinesCount <= 2; $newlinesCount++) {
            $newlines = str_repeat("\n", $newlinesCount);
            $dom = self::getDom(
                "<checkstyle>\n<error/>{$newlines}</checkstyle>"
            );
            $node = self::getRootNodeForDom($dom);
            $node->rtrim();

            $expected =
                self::getCompleteXml("<checkstyle>\n<error/></checkstyle>");

            self::assertEquals(
                $expected,
                $dom->saveXML()
            );
        }
    }

    public function testTrimOnWhitespaceMakesNodeEmpty()
    {
        $node = self::getRootNodeForXml("<checkstyle>\n</checkstyle>");
        $node->trim();
        self::assertTrue($node->isEmpty());
    }

    public function testIsTagNameEqualTo()
    {
        $node = self::getRootNodeForXml("<checkstyle/>");
        self::assertTrue($node->isTagNameEqualTo('checkstyle'));
        self::assertFalse($node->isTagNameEqualTo('file'));
    }

    public function testIsAttributeEqualTo()
    {
        $expectedVersion = '3.1.1';
        $node = self::getRootNodeForXml(
            "<checkstyle version='$expectedVersion' />"
        );
        self::assertEquals($expectedVersion, $node->getAttribute('version'));
    }

    public function testComparingMissingAttributeTriggersException()
    {
        $this->expectException(Exception::class);
        self::getRootNodeForXml('<checkstyle/>')->getAttribute('version');
    }

    public function testGetChildren()
    {
        $node = self::getRootNodeForXml(
            "<checkstyle>\n<file>\n</file>\n\n<test/>\n\n</checkstyle>"
        );

        // Yes, they are expected in reverse order
        // We don't really care about the order they're traversed
        // And reverse order allows simple modifications of a tree on the fly
        $expectedNodes = ['test', 'file'];

        $i = 0;
        foreach ($node->getChildren() as $child) {
            self::assertInstanceOf(Node::class, $child);
            self::assertTrue($child->isTagNameEqualTo($expectedNodes[$i]));
            ++$i;
        }
    }

    public function testSetAttribute()
    {
        $xml = '<checkstyle/>';
        $expectedXml = '<checkstyle version="3.0.0"/>';
        $dom = self::getDom($xml);
        $node = self::getRootNodeForDom($dom);
        $node->setAttribute('version', '3.0.0');

        self::assertXmlStringEqualsXmlString(
            self::getCompleteXml($expectedXml),
            $dom->saveXML()
        );
    }

    private static function getDom($xml)
    {
        $dom = new DOMDocument();
        $dom->loadXML($xml);

        return $dom;
    }

    private static function getRootNodeForXml($xml)
    {
        return self::getRootNodeForDom(
            self::getDom(self::getCompleteXml($xml))
        );
    }

    private static function getCompleteXml($xml)
    {
        $header = '<?xml version="1.0"?>';

        return "{$header}\n{$xml}\n";
    }

    private static function getRootNodeForDom(DOMDocument $dom)
    {
        return new Node($dom->documentElement);
    }
}
