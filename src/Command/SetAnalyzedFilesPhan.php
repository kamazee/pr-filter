<?php

namespace Kamazee\PrFilter\Command;

use Kamazee\PrFilter\Phan\Config;

class SetAnalyzedFilesPhan
{
    private $reader;

    public function __construct(FileListReader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * @param string $infile
     * @param string|null $outfile
     *
     * @throws \Kamazee\PrFilter\Phan\ConfigException
     */
    public function __invoke(string $infile, string $outfile = null)
    {
        if (null === $outfile) {
            $outfile = $infile;
        }

        $analyzedFiles = $this->reader->read('php://stdin');

        $config = new Config($infile);
        $config->setAnalyzedFiles($analyzedFiles);
        $config->write($outfile);
    }
}
