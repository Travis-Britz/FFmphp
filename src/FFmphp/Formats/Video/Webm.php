<?php

namespace FFmphp\Formats\Video;

use FFmphp\Formats\InteractsWithOutput;
use FFmphp\Formats\OutputFormat;

/**
 * Class Webm
 *
 * Sets options consistent with a Webm container format. This class is included as
 * as a reference, since it leaves most options to their default settings
 * and may not produce optimally encoded videos.
 */
class Webm implements OutputFormat
{
    use InteractsWithOutput;

    public function build()
    {
        return $this->withOptions([
            '-vcodec' => 'libvpx-vp9',
            '-acodec' => 'libopus',
        ]);
    }
}
