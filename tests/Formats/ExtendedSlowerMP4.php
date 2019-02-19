<?php

namespace Tests\Formats;

use FFmphp\Formats\Video\MP4;

class ExtendedSlowerMP4 extends MP4
{
    public function build()
    {
        return parent::build()->withOption('-preset', 'slower');
    }
}