<?php


namespace Tests;

use FFmphp\FFmphp;
use FFmphp\Formats\Audio\MP3;
use FFmphp\Formats\Image\Poster;
use FFmphp\Formats\Image\TileFiveByFive;
use FFmphp\Formats\Image\TileFourByThree;
use FFmphp\Formats\NullFormat;
use FFmphp\Formats\Video\HLS;
use FFmphp\Formats\Video\MP4;
use FFmphp\Formats\Video\Webm;
use Tests\Formats\ExtendedH265MP4;
use Tests\Formats\ExtendedHLS_3s;
use Tests\Formats\ExtendedSlowerMP4;

class OutputFormatTest extends BaseTestCase
{
    public function test_format_mp4()
    {
        $command = FFmphp::load('in')
                         ->save('out', MP4::class)
                         ->toCommand();

        $this->assertCommandEquals('ffmpeg -i in -vcodec libx264 -acodec aac out', $command);
    }

    public function test_format_webm()
    {
        $command = FFmphp::load('in')
                         ->save('out', Webm::class)
                         ->toCommand();

        $this->assertCommandEquals('ffmpeg -i in -vcodec libvpx-vp9 -acodec libopus out', $command);
    }

    public function test_format_mp3()
    {
        $command = FFmphp::load('in')
                         ->save('out', MP3::class)
                         ->toCommand();

        $this->assertCommandEquals('ffmpeg -i in -vn -acodec libmp3lame out', $command);
    }

    public function test_format_null()
    {
        $command = FFmphp::load('in')
                         ->save('out', NullFormat::class)
                         ->toCommand();

        $this->assertCommandEquals('ffmpeg -i in out', $command);
    }

    public function test_extending_mp4_contains_new_options()
    {
        $command = FFmphp::load('in')
                         ->save('out', ExtendedSlowerMP4::class)
                         ->toCommand();

        $this->assertCommandEquals('ffmpeg -i in -vcodec libx264 -acodec aac -preset slower out', $command);
    }

    public function test_extending_mp4_replaces_previous_option()
    {
        $command = FFmphp::load('in')
                         ->save('out', ExtendedH265MP4::class)
                         ->toCommand();

        $this->assertCommandEquals('ffmpeg -i in -vcodec libx265 -acodec aac -preset slower out', $command);
    }

    public function test_format_video_poster()
    {
        $command = FFmphp::load('in')
                         ->save('out', Poster::class)
                         ->toCommand();

        $this->assertCommandEquals('ffmpeg -i in -filter:v thumbnail,thumbnail -frames:v 1 -vsync vfr out', $command);
    }

    public function test_format_tile_five_by_five()
    {
        $command = FFmphp::load('in')
                         ->save('out', TileFiveByFive::class)
                         ->toCommand();

        $this->assertCommandEquals('ffmpeg -i in -filter:v thumbnail,tile=5x5 -frames:v 1 -vsync vfr out', $command);
    }

    public function test_format_tile_four_by_three()
    {
        $command = FFmphp::load('in')
                         ->save('out', TileFourByThree::class)
                         ->toCommand();

        $this->assertCommandEquals('ffmpeg -i in -filter:v thumbnail,tile=4x3 -frames:v 1 -vsync vfr out', $command);
    }

    public function test_format_hls()
    {
        $command = FFmphp::load('in')
                         ->save('out', HLS::class)
                         ->toCommand();

        $this->assertCommandEquals('ffmpeg -i in -codec:v h264 -codec:a aac -hls_playlist_type vod -hls_time 2 -force_key_frames expr:gte(t,n_forced*2) out', $command);
    }

    public function test_format_hls_extended_class_overrides_settings()
    {
        $command = FFmphp::load('in')
                         ->save('out', ExtendedHLS_3s::class)
                         ->toCommand();

        $this->assertCommandEquals('ffmpeg -i in -codec:v h264 -codec:a aac -hls_playlist_type vod -hls_time 3 -force_key_frames expr:gte(t,n_forced*3) -bufsize 7500k out', $command);
    }
}