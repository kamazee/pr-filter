<?php

namespace Kamazee\PrFilter\Diff;

use ptlis\DiffParser\Parser;

class Factory
{
    private $parser;

    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }

    public function git($filename)
    {
        return new Diff(
            $this->parser->parseFile($filename, Parser::VCS_GIT)
        );
    }
}
