<?php

namespace FFmphp;

use FFmphp\Formats\NullFormat;
use FFmphp\Formats\OutputFormat;
use Symfony\Component\Process\Process;

class RawCommandBuilder
{
    use RunsCommands;

    /**
     * The raw array of options and arguments for the command.
     *
     * @var array
     */
    protected $arguments = [];

    /**
     * Get the full command as an array that can be used by the Symfony Process.
     *
     * @return array
     */
    public function toArray()
    {
        return array_merge([$this->command], $this->arguments);
    }

    /**
     * Set the raw array of options and arguments to pass to the command.
     *
     * @param array $arguments
     *
     * @return $this
     */
    public function withArguments($arguments)
    {
        $this->arguments = $arguments;
        return $this;
    }
}
