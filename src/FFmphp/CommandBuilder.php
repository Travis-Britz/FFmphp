<?php

namespace FFmphp;

use FFmphp\Formats\NullFormat;
use FFmphp\Formats\OutputFormat;
use Symfony\Component\Process\Process;

class CommandBuilder
{
    use HasOptions, RunsCommands;

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
            // "-i" is added last because it needs to directly precede the url
            ->withOption('-i')
            ->url($stream_url);

        return $this;
    }
}
