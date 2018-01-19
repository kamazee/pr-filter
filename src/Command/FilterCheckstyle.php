<?php

namespace Kamazee\PrFilter\Command;

use Kamazee\PrFilter\Checkstyle\Filter;
use Kamazee\PrFilter\Diff\Factory;
use Kamazee\PrFilter\Xml\Loader;
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
