<?php

namespace FFmphp\Formats\Image;

use FFmphp\Formats\InteractsWithOutput;
use FFmphp\Formats\OutputFormat;

class Poster implements OutputFormat
{
    use InteractsWithOutput;

    public function build()
    {
        return $this->withOptions([
            '-filter:v' => 'thumbnail,thumbnail',
//            '-ss' => '0',
            '-frames:v' => '1',
            '-vsync' => 'vfr', // https://superuser.com/questions/538112/meaningful-thumbnails-for-a-video-using-ffmpeg
        ]);
    }
}