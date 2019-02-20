<?php

namespace FFmphp\Formats\Image;

use FFmphp\Formats\InteractsWithOutput;
use FFmphp\Formats\OutputFormat;

class TileFourByThree implements OutputFormat
{
    use InteractsWithOutput;

    public function build()
    {
        return $this->withOptions([
            '-filter:v' => 'thumbnail,tile=4x3',
            '-frames:v' => '1',
            '-vsync' => 'vfr',
        ]);

    }
}