<?php

namespace FFmphp;

use FFmphp\Formats\NullFormat;
use FFmphp\Formats\OutputFormat;
use Symfony\Component\Process\Process;

class CommandBuilder
{

    /**
     * The global options for FFmpeg.
     *
     * @var array
     */
    protected $options = [];

    /**
     * The input streams to FFmpeg.
     *
     * @var array
     */
    protected $input_streams = [];

    /**
     * The output streams for FFmpeg.
     *
     * @var array
     */
    protected $output_streams = [];

    /**
     * The command to run.
     *
     * @var string
     */
    protected $command;

    /**
     * The number of seconds the command will be allowed to run. "null" or "0" means no limit.
     *
     * @var int
     */
    protected $timeout;

    /**
     * Attaches a new output stream to the command.
     *
     * @param string                              $destination The url for the output stream. Usually this is a file
     *                                                         name, but any protocol supported by FFmpeg can be used
     *                                                         (ffmpeg -protocols).
     * @param \FFmphp\Formats\OutputFormat|string $format      The name of the class responsible for setting the
     *                                                         options used by the output.
     * @param array|Callable                      $options     An array of options to be added to the current
     *                                                         StreamBuilder, or a closure which will receive the
     *                                                         current StreamBuilder instance.
     *
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
            $options($builder);
        }

        $this->output_streams[] = $builder->url($destination);

        return $this;
    }

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

    /**
     * Get the full command as an array that can be used by the Symfony Process.
     *
     * @return array
     */
    public function toArray()
    {
        return array_merge(
            [$this->command],
            $this->getOptionsArray(),
            $this->getInputStreamsArray(),
            $this->getOutputStreamsArray()
        );
    }

    /**
     * Get an array of the global ffmpeg options.
     *
     * @return array
     */
    public function getOptionsArray()
    {
        $command = [];
        foreach ($this->options as $key => $value) {
            if ($value !== false) {
                $command[] = $key;
            }
            if (!is_bool($value)) {
                $command[] = $value;
            }
        }

        return $command;

    }

    /**
     * Get all input streams and their options as an array.
     *
     * @return array
     */
    public function getInputStreamsArray()
    {
        $array = [];
        foreach ($this->input_streams as $stream) {
            $array = array_merge($array, $stream->toArray());
        }
        return $array;
    }

    /**
     * Get all output streams and their options as an array.
     *
     * @return array
     */
    public function getOutputStreamsArray()
    {
        $array = [];
        foreach ($this->output_streams as $stream) {
            $array = array_merge($array, $stream->toArray());
        }
        return $array;
    }

    /**
     * Get the full FFmpeg command as it would be used on the command line.
     *
     * @return string
     */
    public function toCommand()
    {
        $process = new Process($this->toArray());

        return $process->getCommandLine();
    }

    /**
     * Conditionally chain method calls onto the current CommandBuilder instance.
     *
     * @param bool     $condition
     * @param Callable $callback The function called when $condition is true. It will receive the current command
     *                           builder instance as its first argument.
     *
     * @return $this
     */
    public function when($condition, Callable $callback)
    {
        if ($condition) {
            $callback($this);
        }

        return $this;
    }

    /**
     * Add an array of global options for the ffmpeg command.
     *
     * For options that do not have a value (such as "-y"), use a boolean value "true" to add the option
     * or "false" to remove it.
     *
     * @param array $options
     *
     * @return $this
     */
    public function withOptions(array $options)
    {
        $builder = $this;
        foreach ($options as $option => $value) {
            $builder = $builder->withOption($option, $value);
        }
        return $builder;
    }

    /**
     * Add a global option to the ffmpeg command.
     *
     * Use a boolean value of "false" to remove an option, or "true" if it does not
     * accept a value.
     *
     * @param             string $option
     * @param bool|string        $value
     *
     * @return $this
     */
    public function withOption($option, $value = true)
    {
        $this->options[$option] = $value;
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
     * Add an input stream to the command.
     *
     * @param       string $stream_url     The input url. Usually a file name, but any protocol supported by FFmpeg is
     *                                     allowed (ffmpeg -protocols).
     * @param array        $stream_options Additional options to apply to the input stream.
     *
     * @return $this
     */
    public function withInput($stream_url, $stream_options = [])
    {
        $this->input_streams[] = (new StreamBuilder)
            ->withOptions($stream_options)
            ->withOption('-i')
            ->url($stream_url);

        return $this;
    }
}
