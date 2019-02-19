<?php

namespace FFmphp\Formats\Image;

use FFmphp\Formats\InteractsWithOutput;
use FFmphp\Formats\OutputFormat;

class TileFiveByFive implements OutputFormat
{
    use InteractsWithOutput;

    public function build()
    {
        return $this->withOptions([
            '-filter:v' => 'thumbnail,tile=5x5',
            '-frames:v' => '1',
            '-vsync' => 'vfr',
        ]);

    }
}