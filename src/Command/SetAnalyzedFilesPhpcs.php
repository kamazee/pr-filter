<?php

namespace Kamazee\PhpqaReportTool\Command;

use Kamazee\PhpqaReportTool\Phpcs\ConfigFactory;

class SetAnalyzedFilesPhpcs
{
    private $fileListReader;
    private $factory;

    public function __construct(
        FileListReader $fileListReader,
        ConfigFactory $factory
    ) {
        $this->fileListReader = $fileListReader;
        $this->factory = $factory;
    }

    /**
     * @param string $dist
     * @param string $output
     * @throws \Kamazee\PhpqaReportTool\Phpcs\ConfigException
     */
    public function __invoke(string $dist, string $output)
    {
        if (null === $output) {
            $output = $dist;
        }

        $analyzedFiles = $this->fileListReader->read('php://stdin');
        $config = $this->factory->createWithFilename($dist);
        $config->setAnalyzedFiles($analyzedFiles);
        $config->write($output);
    }
}
