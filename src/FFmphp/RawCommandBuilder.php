<?php

namespace FFmphp;

use FFmphp\Formats\NullFormat;
use FFmphp\Formats\OutputFormat;
use Symfony\Component\Process\Process;

class RawCommandBuilder
{

    protected $arguments = [];

    protected $command;

    protected $timeout;


    /**
     * @param callable|null $callback
     * @throws \Symfony\Component\Process\Exception\ProcessFailedException
     */
    public function run(Callable $callback = null)
    {
        $process = new Process($this->toArray());
        $process->setTimeout($this->timeout);
        $process->mustRun(function ($type, $buffer) use ($callback) {
            if (Process::ERR === $type) {
                //frame=   55 fps=9.2 q=0.0 q=0.0 q=0.0 q=14.4 q=0.0 q=0.0 size=       0kB time=00:00:02.61 bitrate=   0.1kbits/s speed=0.254x
                if (is_callable($callback) && (preg_match('/size=.*? time=(.*?) /', $buffer, $matches))) {
                    $callback($matches[1]);
                }
            }
        });
    }

    public function toArray()
    {
        return array_merge([$this->command], $this->arguments);
    }

    /**
     * @return string
     */
    public function toCommand()
    {
        $process = new Process($this->toArray());
        return $process->getCommandLine();
    }

    public function command($command)
    {
        $this->command = $command;
        return $this;
    }

    public function withArguments($arguments)
    {
        $this->arguments = $arguments;
        return $this;
    }

    public function timeoutAfter($timeout)
    {
        $this->timeout = $timeout;
        return $this;
    }
}
