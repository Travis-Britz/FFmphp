<?php

namespace FFmphp;

class FFmphp
{

    /**
     * The path to the ffmpeg binary.
     *
     * @var string
     */
    public static $ffmpeg = 'ffmpeg';

    /**
     * The path to the ffprobe binary.
     *
     * @var string
     */
    public static $ffprobe = 'ffprobe';

    /**
     * The number of seconds a process will be allowed to run, after which a
     * \Symfony\Component\Process\Exception\ProcessTimedOutException
     * will be thrown.
     *
     * A value of "null" or "0" indicates there is no limit.
     *
     * @var int
     */
    public static $timeout = 0;


    /**
     * Set global defaults for FFmphp
     *
     * Supported options:
     *
     * "ffmpeg" - The path to the ffmpeg binary
     * "ffprobe" - The path to the ffprobe binary
     * "timeout" - The time limit for a running process
     *
     * @param array $options
     */
    public static function configure(array $options)
    {
        static::$ffmpeg = $options['ffmpeg'] ?? static::$ffmpeg;
        static::$ffprobe = $options['ffprobe'] ?? static::$ffprobe;
        static::$timeout = $options['timeout'] ?? static::$timeout;
    }

    /**
     * Prepare a new CommandBuilder instance for a given input source.
     *
     * @param string $input_stream  The url for the input stream. Usually this is a file name, but any protocol
     *                              supported by FFmpeg can be used (ffmpeg -protocols).
     * @param array  $input_options An array of additional options for the input stream.
     *
     * @return \FFmphp\CommandBuilder
     */
    public static function load($input_stream, $input_options = [])
    {
        return (new CommandBuilder)
            ->command(static::$ffmpeg)
            ->withInput($input_stream, $input_options)
            ->timeoutAfter(static::$timeout);
    }

    /**
     * Prepare a new raw command.
     *
     * @param array $arguments An array of arguments which will be escaped and passed directly to FFmpeg.
     *
     * @return \FFmphp\RawCommandBuilder
     */
    public static function raw(array $arguments)
    {
        return (new RawCommandBuilder)
            ->command(static::$ffmpeg)
            ->withArguments($arguments)
            ->timeoutAfter(static::$timeout);
    }
}
