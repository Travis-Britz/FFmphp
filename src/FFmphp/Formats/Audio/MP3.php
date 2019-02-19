<?php

namespace FFmphp\Formats\Audio;

use FFmphp\Formats\InteractsWithOutput;
use FFmphp\Formats\OutputFormat;

class MP3 implements OutputFormat
{
    use InteractsWithOutput;

    public function build()
    {
        return $this
            ->withOption('-vn', true)
            ->withOption('-acodec', 'libmp3lame');
    }
}
