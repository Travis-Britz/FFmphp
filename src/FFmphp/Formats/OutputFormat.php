<?php

namespace FFmphp\Formats;

interface OutputFormat
{

    /**
     * This method is responsible for preparing the options
     * that will be passed to an FFmpeg output stream.
     *
     * @return \FFmphp\StreamBuilder
     */
    public function build();
}
