<?php

namespace Tests\Formats;

use FFmphp\Formats\Video\MP4;

class ExtendedH265MP4 extends MP4
{
    public function build()
    {
        return parent::build()->withOptions([
            '-vcodec' => 'libx265',
            '-preset'=> 'slower'
        ]);
    }
}