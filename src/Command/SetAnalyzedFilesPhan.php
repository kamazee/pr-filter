<?php

namespace Kamazee\PhpqaReportTool\Command;

use Kamazee\PhpqaReportTool\Phan\Config;
use Symfony\Component\Console\Input\InputInterface;

class SetAnalyzedFilesPhan
{
    private $reader;

    public function __construct(FileListReader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * @param InputInterface $input
     * @param string $dist
     * @param string|null $output
     *
     * @throws \Kamazee\PhpqaReportTool\Phan\ConfigException
     */
    public function __invoke(InputInterface $input, string $dist, string $output = null)
    {
        if (null === $output) {
            $output = $dist;
        }

        $analyzedFiles = $this->reader->read('php://stdin');

        $config = new Config($dist);
        $config->setAnalyzedFiles($analyzedFiles);
        $config->write($output);
    }
}
