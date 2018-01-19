<?php

namespace Kamazee\PrFilter\Phan;

use Kamazee\PrFilter\Filesystem\File;
use Kamazee\PrFilter\Filesystem\FileException;
use function error_get_last;
use function file_put_contents;
use function var_export;

class Config
{
    private $filename;
    private $config;

    /**
     * @param string $filename
     *
     * @throws ConfigException
     */
    public function __construct(string $filename = null)
    {
        $this->filename = $filename;

        if (null === $filename) {
            $this->config = [];
            return;
        }

        $this->loadConfig($filename);
    }

    /**
     * @param string $filename
     *
     * @throws ConfigException
     */
    private function loadConfig(string $filename)
    {
        try {
            (new File($filename))->assertFileIsReadable();
        } catch (FileException $e) {
            throw ConfigException::cantReadConfig($e);
        }

        $config = require $filename;

        if (!is_array($config)) {
            throw ConfigException::configIsNotAnArray($filename);
        }

        $this->config = $config;
    }

    /**
     * @param string[] $filenames
     */
    public function setAnalyzedFiles(array $filenames)
    {
        $this->config['include_analysis_file_list'] = $filenames;
    }

    /**
     * @param string|null $filename
     *
     * @throws ConfigException
     */
    public function write(string $filename): void
    {
        $result = @file_put_contents(
            $filename,
            "<?php\n\nreturn " . var_export($this->config, true) . ';'
        );

        if (!$result) {
            $error = error_get_last() + ['message' => 'Unknown error'];
            throw ConfigException::writingConfigFailed($filename, $error);
        }
    }
}
