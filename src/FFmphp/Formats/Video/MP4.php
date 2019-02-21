<?php

namespace FFmphp\Formats\Video;

use FFmphp\Formats\InteractsWithOutput;
use FFmphp\Formats\OutputFormat;

/**
 * Class MP4
 *
 * Sets options consistent with an MP4 container format. This class is included as
 * as a reference, since it leaves most options to their default settings
 * and may not produce optimally encoded videos.
 */
class MP4 implements OutputFormat
{
    use InteractsWithOutput;

    public function build()
    {
        return $this->withOptions([
            '-vcodec' => 'libx264',
            '-acodec' => 'aac',
        ]);
    }
}
