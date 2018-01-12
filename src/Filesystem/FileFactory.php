<?php

namespace Kamazee\PhpqaReportTool\Filesystem;

class FileFactory
{
    public function file(string $filename): File
    {
        return new File($filename);
    }
}
