<?php

namespace FFmphp\Formats\Video;

use FFmphp\Formats\InteractsWithOutput;
use FFmphp\Formats\OutputFormat;

class MP4 implements OutputFormat
{
    use InteractsWithOutput;

    public function build()
    {
        return $this
            ->withOption('-vcodec', 'libx264')
            ->withOption('-acodec', 'aac');
    }
}
