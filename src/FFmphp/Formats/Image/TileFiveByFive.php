<?php

namespace FFmphp\Formats\Image;

use FFmphp\Formats\InteractsWithOutput;
use FFmphp\Formats\OutputFormat;

/**
 * Class TileFiveByFive
 *
 * This class applies filters to create a 5x5 tiled image.
 */
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