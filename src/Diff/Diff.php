<?php

namespace Kamazee\PrFilter\Diff;

use ptlis\DiffParser\Changeset;
use ptlis\DiffParser\File;
use ptlis\DiffParser\Line;

class Diff
{
    private $files;

    public function __construct(Changeset $changeset)
    {
        $files = [];

        foreach ($changeset->getFiles() as $file) {
            if (File::DELETED === $file->getOperation()) {
                continue;
            }

            foreach ($file->getHunks() as $hunk) {
                foreach ($hunk->getLines() as $line) {
                    if (Line::REMOVED === $line->getOperation()) {
                        continue;
                    }

                    if (Line::UNCHANGED === $line->getOperation()) {
                        continue;
                    }

                    $filename = $file->getNewFilename();
                    $lineNo = $line->getNewLineNo();
                    $files[$filename][$lineNo] = $line;
                }
            }
        }

        $this->files = $files;
    }

    public function isNewCode($file, $line)
    {
        return isset($this->files[$file][$line]);
    }
}
