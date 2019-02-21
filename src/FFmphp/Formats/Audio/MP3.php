<?php

namespace FFmphp\Formats\Audio;

use FFmphp\Formats\InteractsWithOutput;
use FFmphp\Formats\OutputFormat;

/**
 * Class MP3
 *
 * This class applies filters to remove video streams and save the output as MP3.
 */
class MP3 implements OutputFormat
{
    use InteractsWithOutput;

    public function build()
    {
        return $this->withOption('-vn')
                    ->withOption('-acodec', 'libmp3lame');
    }
}
