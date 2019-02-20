<?php

namespace FFmphp\Formats;

interface OutputFormat
{

    /**
     * This method is responsible for preparing the options
     * that will be passed to an FFmpeg output stream.
     *
     * @return \FFmphp\Formats\OutputBuilder
     */
    public function build();
}
