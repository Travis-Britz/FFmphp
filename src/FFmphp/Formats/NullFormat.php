<?php

namespace FFmphp\Formats;

/**
 * Class NullFormat
 *
 * This format applies no options to the output, and is used as the
 * default when no format is specified.
 */
class NullFormat implements OutputFormat
{
    public function build()
    {
        return (new OutputBuilder);
    }
}
