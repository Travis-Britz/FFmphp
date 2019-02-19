<?php

namespace FFmphp\Formats\Image;

use FFmphp\Formats\OutputBuilder;
use FFmphp\Formats\OutputFormat;

class TileFourByThree implements OutputFormat
{
    public function build()
    {
        return (new OutputBuilder)->withOptions([
            '-filter:v' => 'thumbnail,tile=4x3',
            '-frames:v' => '1',
            '-vsync' => 'vfr',
        ]);

    }
}