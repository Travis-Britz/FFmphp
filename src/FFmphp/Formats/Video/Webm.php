<?php

namespace FFmphp\Formats\Video;

use FFmphp\Formats\InteractsWithOutput;
use FFmphp\Formats\OutputFormat;

class Webm implements OutputFormat
{
    use InteractsWithOutput;

    public function build()
    {
        return $this
            ->withOption('-vcodec', 'libvpx-vp9')
            ->withOption('-acodec', 'libopus');
    }
}
