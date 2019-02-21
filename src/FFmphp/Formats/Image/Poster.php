<?php

namespace FFmphp\Formats\Image;

use FFmphp\Formats\InteractsWithOutput;
use FFmphp\Formats\OutputFormat;

/**
 * Class Poster
 *
 * This class applies filters to create an image that can be used as the poster of a video element.
 */
class Poster implements OutputFormat
{
    use InteractsWithOutput;

    public function build()
    {
        return $this->withOptions([

            // A single pass through the thumbnail filter usually produces an image from title screens during the first
            // few seconds of a video. A second pass takes longer but may produce a more representative image.
            '-filter:v' => 'thumbnail,thumbnail',

            // The number of images to save.
            '-frames:v' => '1',

            // https://superuser.com/questions/538112/meaningful-thumbnails-for-a-video-using-ffmpeg
            '-vsync' => 'vfr',
        ]);
    }
}