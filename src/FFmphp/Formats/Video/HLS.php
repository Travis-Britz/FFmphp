<?php

namespace FFmphp\Formats\Video;

use FFmphp\Formats\InteractsWithOutput;
use FFmphp\Formats\OutputFormat;

/**
 * Class HLS
 *
 * Sets options consistent with an HLS (Apple HTTP Live Streaming) format. This class is included as
 * as a reference, since it leaves most options to their default settings
 * and may not produce optimally encoded videos for all use cases.
 */
class HLS implements OutputFormat
{
    use InteractsWithOutput;

    /**
     * The .ts segment file duration, in seconds.
     *
     * The default for FFmpeg is 2 seconds, but we set it explicitly to ensure it remains
     * consistent with the forced key frame interval
     *
     * @var int
     */
    protected $segment_length = 2;

    /**
     * Build the output options.
     *
     */
    public function build()
    {
        return $this->withOption('-codec:v', 'h264')
                    ->withOption('-codec:a', 'aac')
                    ->withOption('-hls_playlist_type', 'vod')
                    ->withOption('-hls_time', $this->segment_length)
                    ->withOption('-force_key_frames', "expr:gte(t,n_forced*{$this->segment_length})");
    }
}
