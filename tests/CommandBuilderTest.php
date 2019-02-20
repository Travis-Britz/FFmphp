<?php

namespace Tests;

use FFmphp\CommandBuilder;
use FFmphp\FFmphp;
use FFmphp\Formats\Audio\MP3;
use FFmphp\Formats\NullFormat;
use FFmphp\Formats\Video\MP4;
use FFmphp\Formats\OutputBuilder;
use FFmphp\StreamBuilder;
use Symfony\Component\Process\Exception\ProcessFailedException;

class CommandBuilderTest extends BaseTestCase
{

    public $timestampExpression = '/^-?\d\d:\d\d:\d\d.\d+$/';

    public function test_save_format_accepts_classnames_as_string()
    {
        $command = FFmphp::load('in')
                         ->save('out', MP4::class)
                         ->toCommand();

        $this->assertCommandEquals('ffmpeg -i in -vcodec libx264 -acodec aac out', $command);
    }

    public function test_save_format_accepts_object_instances()
    {
        $command = FFmphp::load('in')
                         ->save('out', new MP4)
                         ->toCommand();

        $this->assertCommandEquals('ffmpeg -i in -vcodec libx264 -acodec aac out', $command);
    }

    public function test_save_accepts_options_array()
    {
        $command = FFmphp::load('in')
                         ->save('out', MP4::class, [
                             '-crf' => 26,
                             '-preset' => 'slower',
                         ])
                         ->toCommand();

        $this->assertCommandEquals('ffmpeg -i in -vcodec libx264 -acodec aac -crf 26 -preset slower out', $command);
    }

    public function test_save_accepts_options_closure()
    {
        $command = FFmphp::load('in')
                         ->save('out', MP4::class, function (StreamBuilder $output) {
                             return $output->withOption('-crf', 26)
                                           ->withOption('-preset', 'slower');
                         })
                         ->toCommand();

        $this->assertCommandEquals('ffmpeg -i in -vcodec libx264 -acodec aac -crf 26 -preset slower out', $command);
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

        $this->assertCommandEquals('ffmpeg -i in -vcodec libx264 -acodec aac out -vcodec libx264 -acodec aac out2 -vn -acodec libmp3lame out3', $command);
    }

    public function test_save_arguments_are_optional()
    {
        $command = FFmphp::load('in')
                         ->save('out')
                         ->toCommand();

        $this->assertCommandEquals('ffmpeg -i in out', $command);
    }

    public function test_raw_builder()
    {
        $command = FFmphp::raw([
            '-y',
            '-i',
            'input.mp4',
            'output.mp4',
        ])->toCommand();

        $this->assertCommandEquals('ffmpeg -y -i input.mp4 output.mp4', $command);

        $command = FFmphp::raw([
            '-y',
            '-i',
            'input.mp4',
            '-i',
            'input2',
            'output.mp4',
        ])->toCommand();

        $this->assertCommandEquals('ffmpeg -y -i input.mp4 -i input2 output.mp4', $command);
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

    public function test_when_method_on_commands_conditionally_applies_callback()
    {
        $command = FFmphp::load('in')
                         ->save('out', MP4::class)
                         ->when(false, function (CommandBuilder $command) {
                             $command->save('out2', MP4::class);
                         })
                         ->toCommand();

        $this->assertCommandEquals('ffmpeg -i in -vcodec libx264 -acodec aac out', $command);

        $command = FFmphp::load('in')
                         ->save('out', MP4::class)
                         ->when(true, function (CommandBuilder $command) {
                             $command->save('out2', MP4::class);
                         })
                         ->toCommand();

        $this->assertCommandEquals('ffmpeg -i in -vcodec libx264 -acodec aac out -vcodec libx264 -acodec aac out2', $command);
    }

    public function test_when_method_on_output_conditionally_applies_callback()
    {
        $command = FFmphp::load('in')
                         ->save('out', MP4::class, function (StreamBuilder $output) {
                             $output->when(false, function (StreamBuilder $output) {
                                 $output->withOption('-b:a', '128k');
                             });
                         })
                         ->toCommand();

        $this->assertCommandEquals('ffmpeg -i in -vcodec libx264 -acodec aac out', $command);

        $command = FFmphp::load('in')
                         ->save('out', MP4::class, function (StreamBuilder $output) {
                             $output->when(true, function (StreamBuilder $output) {
                                 $output->withOption('-b:a', '128k');
                             });
                         })
                         ->toCommand();

        $this->assertCommandEquals('ffmpeg -i in -vcodec libx264 -acodec aac -b:a 128k out', $command);
    }

    public function test_null_output_replaced_on_windows()
    {
        $command = FFmphp::load('in')
                         ->save('/dev/null', NullFormat::class, ['-f' => 'mp4'])
                         ->toCommand();

        if ('\\' == \DIRECTORY_SEPARATOR) {
            $this->assertCommandEquals('ffmpeg -i in -f mp4 NUL', $command);
        } else {
            $this->assertCommandEquals('ffmpeg -i in -f mp4 /dev/null', $command);
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

    public function test_global_command_options_go_first()
    {
        $command = FFmphp::load('in')
                         ->save('out')
                         ->withOption('-y')
                         ->toCommand();

        $this->assertCommandEquals('ffmpeg -y -i in out', $command);
    }

    public function test_multiple_input_streams_with_global_option_and_input_option_maintains_order()
    {
        $command = FFmphp::load('in')
                         ->save('out')
                         ->withInput('testsrc=s=hd720:d=5', ['-f' => 'lavfi'])
                         ->save('out2')
                         ->withOption('-y')
                         ->toCommand();

        $this->assertCommandEquals('ffmpeg -y -i in -f lavfi -i testsrc=s=hd720:d=5 out out2', $command);
    }

    public function test_options_with_false_value_are_not_included()
    {
        $command = FFmphp::load('in')
                         ->save('out')
                         ->withOption('-n')
                         ->withOption('-y', false)
                         ->toCommand();

        $this->assertCommandEquals('ffmpeg -n -i in out', $command);
    }
}