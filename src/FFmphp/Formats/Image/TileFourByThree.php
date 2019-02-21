<?php

namespace FFmphp\Formats\Image;

use FFmphp\Formats\InteractsWithOutput;
use FFmphp\Formats\OutputFormat;

/**
 * Class TileFourByThree
 *
 * This class applies filters to create a 4x3 tiled image.
 */
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