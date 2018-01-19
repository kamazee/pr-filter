<?php

namespace Kamazee\PrFilter\Phpcs;

use DOMDocument;
use DOMElement;
use Kamazee\PrFilter\Xml\Exception;
use Kamazee\PrFilter\Xml\Loader;

class ConfigFactory
{
    private $loader;

    public function __construct(Loader $loader)
    {
        $this->loader = $loader;
    }

    /**
     * @param string $filename
     * @return Config
     * @throws ConfigException
     */
    public function createWithFilename(string $filename)
    {
        return new Config($this->loadConfig($filename));
    }

    public function createEmpty()
    {
        return new Config($this->initConfig());
    }

    private function initConfig(): DOMDocument
    {
        $xml = new DOMDocument();
        $ruleset = new DOMElement('ruleset');
        $xml->appendChild($ruleset);

        return $xml;
    }

    /**
     * @param string $filename
     *
     * @return DOMDocument
     * @throws ConfigException
     */
    private function loadConfig(string $filename): DOMDocument
    {
        try {
            return $this->loader->loadFromFile($filename);
        } catch (Exception $e) {
            throw ConfigException::cantReadConfig($filename, $e);
        }
    }
}
