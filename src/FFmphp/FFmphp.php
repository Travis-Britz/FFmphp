<?php

namespace FFmphp;

class FFmphp
{

    /**
     * @var string
     */
    static $ffmpeg = 'ffmpeg';

    /**
     * @var string
     */
    static $ffprobe = 'ffprobe';

    /**
     * @var int
     */
    static $timeout = 0;

    /**
     *
     * @param $input_stream
     * @param array $input_options
     * @return \FFmphp\CommandBuilder
     */
    public static function load($input_stream, $input_options = [])
    {
        return (new CommandBuilder)
            ->command(static::$ffmpeg)
            ->withInput($input_stream, $input_options)
            ->timeoutAfter(static::$timeout);
    }

    public static function configure($options)
    {
        static::$ffmpeg = $options['ffmpeg'] ?? 'ffmpeg';
        static::$ffprobe = $options['ffprobe'] ?? 'ffprobe';
        static::$timeout = $options['timeout'] ?? 0;
    }

    /**
     * @param $arguments
     * @return \FFmphp\RawCommandBuilder
     */
    public static function raw($arguments)
    {
        return (new RawCommandBuilder)
            ->command(static::$ffmpeg)
            ->withArguments($arguments)
            ->timeoutAfter(static::$timeout);
    }
}
