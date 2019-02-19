<?php

namespace FFmphp;

use FFmphp\Formats\NullFormat;
use FFmphp\Formats\OutputFormat;
use Symfony\Component\Process\Process;

class CommandBuilder
{

    protected $arguments = [];

    protected $outputs = [];

    protected $command;

    protected $timeout = 0;

    /**
     * @param $destination
     * @param \FFmphp\Formats\OutputFormat|string $format
     * @param array|Callable $options
     * @return $this
     */
    public function save($destination, $format = NullFormat::class, $options = [])
    {
        if (!is_subclass_of($format, OutputFormat::class)) {
            throw new \InvalidArgumentException("Expected format to be subclass of " . OutputFormat::class . ", " . gettype($format) . " given.");
        }

        $builder = call_user_func([new $format, 'build']);

        if (is_array($options)) {
            $builder = $builder->withOptions($options);
        } elseif (is_callable($options)) {
            $builder = $options($builder);
        }

        $this->outputs[] = $builder->destination($destination);

        return $this;
    }

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
        $command = [$this->command];

        foreach ($this->arguments as $argument => $value) {
            $command[] = $argument;
            if ($value !== true) {
                $command[] = $value;
            }
        }

        foreach ($this->outputs as $output) {
            $command = array_merge($command, $output->toArray());
        }

        return $command;
    }

    /**
     * @return string
     */
    public function toCommand()
    {
        $process = new Process($this->toArray());

        return $process->getCommandLine();
    }

    /**
     * @param bool $condition
     * @param Callable $callback
     * @return \FFmphp\CommandBuilder
     */
    public function when($condition, Callable $callback)
    {
        if ($condition) {
            $callback($this);
        }

        return $this;
    }

    public function withOptions($options)
    {
        $builder = $this;
        foreach ($options as $option => $value) {
            $builder = $builder->withOption($option, $value);
        }
        return $builder;
    }

    /**
     * @param $option
     * @param bool|string $value
     * @return $this
     */
    public function withOption($option, $value = true)
    {
        $this->arguments[$option] = $value;
        return $this;
    }

    public function command($command)
    {
        $this->command = $command;
        return $this;
    }

    public function timeoutAfter($timeout)
    {
        $this->timeout = $timeout;
        return $this;
    }
}
