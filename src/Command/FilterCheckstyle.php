<?php

namespace Kamazee\PhpqaReportTool\Command;

use Kamazee\PhpqaReportTool\Checkstyle\Filter;
use Kamazee\PhpqaReportTool\Diff\Factory;
use Kamazee\PhpqaReportTool\Xml\Loader;
use Symfony\Component\Console\Output\OutputInterface;

class FilterCheckstyle
{
    private $factory;
    private $loader;

    public function __construct(Loader $loader, Factory $diffFactory)
    {
        $this->factory = $diffFactory;
        $this->loader = $loader;
    }

    public function __invoke(
        $diff,
        $infile,
        OutputInterface $output,
        $outfile = null,
        $basePath = null
    ) {
        if (null == $outfile) {
            $outfile = $infile;
        }

        $processedDiff = $this->factory->git($diff);

        (new Filter($this->loader, $processedDiff))
            ->filter($infile, $outfile, $basePath);
    }
}
