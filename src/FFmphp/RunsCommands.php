<?php

namespace FFmphp;

use Symfony\Component\Process\Process;

trait RunsCommands
{
    /**
     * The command to run.
     *
     * @var string
     */
    protected $command;

    /**
     * The number of seconds the process will be allowed to run.
     *
     * @var int
     */
    protected $timeout;

    /**
     * Set the time limit for the process.
     *
     * @param int $timeout The number of seconds to wait before the process times out.
     *
     * @return $this
     */
    public function timeoutAfter($timeout)
    {
        $this->timeout = $timeout;
        return $this;
    }

    /**
     * Set the command to run.
     *
     * @param string $command The command to execute.
     *
     * @return $this
     */
    public function command($command)
    {
        $this->command = $command;
        return $this;
    }

    /**
     * Get the full FFmpeg command as it would be used on the command line.
     *
     * @return string
     */
    public function toCommand()
    {
        return $this->process()->getCommandLine();
    }

    /**
     * @return \Symfony\Component\Process\Process
     */
    public function process()
    {
        $process = new Process($this->toArray());
        $process->setTimeout($this->timeout);
        return $process;
    }

    /**
     * Get the full command as an array that can be used by the Symfony Process.
     *
     * @return array
     */
    abstract public function toArray();

    /**
     * Runs the FFmpeg command.
     *
     * @param callable|null $callback A function to be called every time FFmpeg prints a new status line, which occurs
     *                                approximately once every second. The function receives the current time of the
     *                                input stream.
     *
     * @throws \Symfony\Component\Process\Exception\ProcessFailedException
     * @throws \Symfony\Component\Process\Exception\ProcessTimedOutException
     */
    public function run(Callable $callback = null)
    {
        $this->process()
             ->mustRun(function ($type, $buffer) use ($callback) {
                 if (Process::ERR === $type) {
                     //frame=   55 fps=9.2 q=0.0 q=0.0 q=0.0 q=14.4 q=0.0 q=0.0 size=       0kB time=00:00:02.61 bitrate=   0.1kbits/s speed=0.254x
                     if (is_callable($callback) && (preg_match('/size=.*? time=(.*?) /', $buffer, $matches))) {
                         $callback($matches[1]);
                     }
                 }
             });
    }
}