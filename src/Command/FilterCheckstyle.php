<?php

namespace Kamazee\PhpqaReportTool\Command;

use Kamazee\PhpqaReportTool\Checkstyle\Filter;
use Kamazee\PhpqaReportTool\Diff\Factory;
use Symfony\Component\Console\Output\OutputInterface;

class FilterCheckstyle
{
    private $factory;

    public function __construct(Factory $diffFactory)
    {
        $this->factory = $diffFactory;
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

        (new Filter($processedDiff))
            ->filter($infile, $outfile, $basePath);
    }
}
