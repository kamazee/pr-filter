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
     * @param string $infile
     * @param string $outfile
     * @throws \Kamazee\PhpqaReportTool\Phpcs\ConfigException
     */
    public function __invoke(string $infile, string $outfile = null)
    {
        if (null === $outfile) {
            $outfile = $infile;
        }

        $analyzedFiles = $this->fileListReader->read('php://stdin');
        $config = $this->factory->createWithFilename($infile);
        $config->setAnalyzedFiles($analyzedFiles);
        $config->write($outfile);
    }
}
