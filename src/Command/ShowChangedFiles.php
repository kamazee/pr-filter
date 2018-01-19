<?php

namespace Kamazee\PrFilter\Command;

use Kamazee\PrFilter\Diff\Factory;
use Symfony\Component\Console\Output\OutputInterface;

class ShowChangedFiles
{
    private $factory;

    public function __construct(Factory $factory)
    {
        $this->factory = $factory;
    }

    public function __invoke($diff, OutputInterface $output)
    {
        $output->writeln(
            $this->factory->git($diff)->getNewAndChangedFiles()
        );
    }
}
