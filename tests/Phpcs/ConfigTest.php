<?php

namespace Kamazee\PrFilter\Phpcs;

use DOMDocument;
use function file_get_contents;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public function testSetsAnalyzedFiles()
    {
        $initialXml = '<ruleset><file>app</file><file>src</file><rule ref="PSR2"/></ruleset>';
        $expectedXml = '<ruleset><rule ref="PSR2"/><file>test.php</file></ruleset>';

        $initialDom = new DOMDocument();
        $initialDom->loadXML($initialXml);

        $vfs = vfsStream::setup();
        $file = "{$vfs->url()}/result.xml";
        $config = new Config($initialDom);
        $config->setAnalyzedFiles(['test.php']);
        $config->write($file);

        $this->assertXmlStringEqualsXmlString(
            $expectedXml,
            file_get_contents($file)
        );
    }
}
