<?php

namespace Tests;

use FFmphp\CommandBuilder;
use FFmphp\FFmphp;
use FFmphp\Formats\Audio\MP3;
use FFmphp\Formats\NullFormat;
use FFmphp\Formats\Video\MP4;
use FFmphp\Formats\OutputBuilder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Exception\ProcessFailedException;

class CommandBuilderTest extends TestCase
{

    public $timestampExpression = '/^-?\d\d:\d\d:\d\d.\d+$/';

    public function test_save_format_accepts_classnames_as_string()
    {
        $command = FFmphp::load('in')
                         ->save('out', MP4::class)
                         ->toCommand();

        $this->assertEquals('ffmpeg -i in -vcodec libx264 -acodec aac out', $command);
    }

    public function test_save_format_accepts_object_instances()
    {
        $command = FFmphp::load('in')
                         ->save('out', new MP4)
                         ->toCommand();

        $this->assertEquals('ffmpeg -i in -vcodec libx264 -acodec aac out', $command);
    }

    public function test_save_accepts_options_array()
    {
        $command = FFmphp::load('in')
                         ->save('out', MP4::class, [
                             '-crf' => 26,
                             '-preset' => 'slower',
                         ])
                         ->toCommand();

        $this->assertEquals('ffmpeg -i in -vcodec libx264 -acodec aac -crf 26 -preset slower out', $command);
    }

    public function test_save_accepts_options_closure()
    {
        $command = FFmphp::load('in')
                         ->save('out', MP4::class, function (OutputBuilder $output) {
                             return $output->withOption('-crf', 26)
                                           ->withOption('-preset', 'slower');
                         })
                         ->toCommand();

        $this->assertEquals('ffmpeg -i in -vcodec libx264 -acodec aac -crf 26 -preset slower out', $command);
    }

    public function test_save_format_rejects_invalid_formats_with_exception()
    {
        $this->expectException(\InvalidArgumentException::class);

        $command = FFmphp::load('in')
                         ->save('out', 'adfasdf')
                         ->toCommand();
    }

//    public function test_config_is_set_for_all_future_calls()
//    {
//
//    }

    public function test_multiple_outputs_are_saved()
    {
        $command = FFmphp::load('in')
                         ->save('out', MP4::class)
                         ->save('out2', MP4::class)
                         ->save('out3', MP3::class)
                         ->toCommand();

        $this->assertEquals('ffmpeg -i in -vcodec libx264 -acodec aac out -vcodec libx264 -acodec aac out2 -vn -acodec libmp3lame out3', $command);
    }

    public function test_save_arguments_are_optional()
    {
        $command = FFmphp::load('in')
                         ->save('out')
                         ->toCommand();

        $this->assertEquals('ffmpeg -i in out', $command);
    }

    public function test_raw_builder()
    {
        $command = FFmphp::raw([
            '-y',
            '-i',
            'input.mp4',
            'output.mp4',
        ])->toCommand();

        $this->assertEquals('ffmpeg -y -i input.mp4 output.mp4', $command);

        $command = FFmphp::raw([
            '-y',
            '-i',
            'input.mp4',
            '-i',
            'input2',
            'output.mp4',
        ])->toCommand();

        $this->assertEquals('ffmpeg -y -i input.mp4 -i input2 output.mp4', $command);
    }

    public function test_run_callback_is_called_with_correct_parameters()
    {
        $call_count = 0;

        FFmphp::load('testsrc=s=hd720:d=5', ['-f' => 'lavfi'])
              ->withOption('-y')
              ->save('/dev/null', NullFormat::class, ['-f' => 'mp4'])
              ->run(function ($time) use (&$call_count) {
                  $call_count++;
                  $this->assertRegExp($this->timestampExpression, $time);
              });

        $this->assertGreaterThanOrEqual(1, $call_count);
    }

    public function test_run_works_without_callback()
    {
        FFmphp::load('testsrc=s=hd720:d=1', ['-f' => 'lavfi'])
              ->withOption('-y')
              ->save('/dev/null', NullFormat::class, ['-f' => 'mp4'])
              ->run();

        $this->assertTrue(true);
    }

    public function test_run_callback_is_called_with_correct_parameters_on_raw()
    {
        $call_count = 0;
        $out = ('\\' == \DIRECTORY_SEPARATOR) ? 'NUL' : '/dev/null';

        FFmphp::raw([
            '-y',
            '-f', 'lavfi',
            '-i', 'testsrc=s=hd720:d=5',
            '-f', 'mp4',
            $out,
        ])->run(function ($time) use (&$call_count) {
            $call_count++;
            $this->assertRegExp($this->timestampExpression, $time);
        });

        $this->assertGreaterThanOrEqual(1, $call_count);
    }

    public function test_run_works_without_callback_on_raw()
    {
        $out = ('\\' == \DIRECTORY_SEPARATOR) ? 'NUL' : '/dev/null';

        FFmphp::raw([
            '-y',
            '-f', 'lavfi',
            '-i', 'testsrc=s=hd720:d=1',
            '-f', 'mp4',
            $out,
        ])->run();

        $this->assertTrue(true);
    }

    public function test_when_method_conditionally_applies_callback()
    {
        $command = FFmphp::load('in')
                         ->save('out', MP4::class)
                         ->when(false, function (CommandBuilder $command) {
                             $command->save('out2', MP4::class);
                         })
                         ->toCommand();

        $this->assertEquals('ffmpeg -i in -vcodec libx264 -acodec aac out', $command);

        $command = FFmphp::load('in')
                         ->save('out', MP4::class)
                         ->when(true, function (CommandBuilder $command) {
                             $command->save('out2', MP4::class);
                         })
                         ->toCommand();

        $this->assertEquals('ffmpeg -i in -vcodec libx264 -acodec aac out -vcodec libx264 -acodec aac out2', $command);
    }

    public function test_null_output_replaced_on_windows()
    {
        $command = FFmphp::load('in')
                         ->save('/dev/null', NullFormat::class, ['-f' => 'mp4'])
                         ->toCommand();

        if ('\\' == \DIRECTORY_SEPARATOR) {
            $this->assertEquals('ffmpeg -i in -f mp4 NUL', $command);
        } else {
            $this->assertEquals('ffmpeg -i in -f mp4 "/dev/null"', $command);
        }
    }

    public function test_ffmpeg_failures_throw_exceptions()
    {
        $this->expectException(ProcessFailedException::class);

        FFmphp::load('testsrc=s=hd720:d=1')
              ->withOption('-y')
              ->save('/dev/null', NullFormat::class, ['-f' => 'mp4'])
              ->run();
    }
}