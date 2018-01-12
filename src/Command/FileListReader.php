<?php

namespace Kamazee\PhpqaReportTool\Command;

class FileListReader
{
    public function read(string $filename)
    {
        $analyzedFiles = file($filename);
        if (false === $analyzedFiles) {
            $analyzedFiles = [];
        } else {
            $analyzedFiles = array_map('trim', $analyzedFiles);
            $analyzedFiles = array_values(array_filter($analyzedFiles));
        }

        return $analyzedFiles;
    }
}
