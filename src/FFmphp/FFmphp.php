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
     * @param $input
     * @param array $options
     * @return \FFmphp\CommandBuilder
     */
    public static function load($input, $options = [])
    {
        return (new CommandBuilder)
            ->command(static::$ffmpeg)
            ->withOptions($options)
            ->withOption('-i', $input)
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
