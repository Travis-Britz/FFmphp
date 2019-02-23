<?php

namespace Tests\Formats;

use FFmphp\Formats\Video\HLS;

class ExtendedHLS_3s extends HLS
{
    protected $segment_length = 3;

    public function build()
    {
        return parent::build()->withOption('-bufsize', '7500k');
    }
}