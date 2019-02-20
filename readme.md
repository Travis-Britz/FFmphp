[![Build Status](https://travis-ci.org/Travis-Britz/FFmphp.svg?branch=master)](https://travis-ci.org/Travis-Britz/FFmphp)

# FFmphp

FFmphp is an object-oriented php wrapper for running FFmpeg. Its goal is to be quick
to learn and integrate for php developers, while being easy to tune for experienced FFmpeg users.

## Installation

You will need a working install of FFmpeg (and FFprobe) in your system (https://www.ffmpeg.org/download.html).

This library can be installed via [composer](https://getcomposer.org/download/):

```shell
$ composer require tbritz/ffmphp
```

As long as `ffmpeg` and `ffprobe` are available from your system's PATH variable, this library should use them automatically. If not, you can configure FFmphp before using it:

```php
FFmphp::configure([
    'ffmpeg' => 'C:\ffmpeg\ffmpeg.exe',
    'ffprobe' => 'C:\ffmpeg\ffprobe.exe',
    'timeout' => 0, // default, no timeout (in seconds)
]);

```

These settings will be applied to all FFmphp calls for the remainder of the script execution.

## Basic Usage

### Video Transcoding

Getting started is easy. Converting a video can be as simple as this command:

```php
FFmphp::load($infile)
    ->save('outfile.mp4')
    ->run();
```

FFmpeg will guess the output type based on the extension.

Most of the time, however, you will tell `save()` how to format the output by passing the name of an `OutputFormat` interface:

```php
FFmphp::load($infile)
    ->save('outfile.mp4', 'FFmphp\Formats\Video\MP4')
    ->run();
```

In the example above we are passing the _fully qualified class name_ to the `save()` method. However, for the rest of the examples we will use php's [::class](https://stackoverflow.com/a/42064777/6038111) syntax instead. Here is what the previous example looks like with this new syntax:

```php
use FFmphp\Formats\Video\MP4;

// ...

FFmphp::load($infile)
    ->save('outfile.mp4', MP4::class)
    ->run();
```

### Extracting Audio Tracks from Video

Working with audio formats is just as easy:

```php
FFmphp::load($infile)
    ->save('outfile.mp3', MP3::class)
    ->run();
```

It is recommended to create a class for each different output format that your application will save. An output format includes bitrate, codec, container type, resolution, and more, but we will cover that later.

To make getting started easier, these basic formats are included:

-   `FFmphp\Formats\Video\MP4`
-   `FFmphp\Formats\Video\Webm`
-   `FFmphp\Formats\Audio\MP3`
-   `FFmphp\Formats\Image\Poster` (Thumbnail)
-   `FFmphp\Formats\Image\TileFiveByFive`
-   `FFmphp\Formats\Image\TileFourByThree`

### Saving Thumbnails

Saving a video poster or thumbnail image works the same way:

```php
FFmphp::load($file)
    ->save('thumbnail.jpg')
    ->run();

FFmphp::load($file)
    ->save('poster.jpg', Poster::class)
    ->run();
```

### Saving Tiled/Mosaic Images

You guessed it:

```php
FFmphp::load($file)
    ->save('preview-4x3.jpg', TileFourByThree::class)
    ->run();
```

## Advanced Usage

### Output Formats

The best way to keep your project organized is to create a class for every type of file that you will save.

You can either create your class from scratch, or you may _extend_ another format. Your class must implement the [`FFmphp\Formats\OutputFormat`](src/FFmphp/Formats/OutputFormat.php) interface, which has one method: `build()`.

Here is an example of creating an MP4 video format that is tuned for animation content by _extending_ the existing MP4 class:

```php
<?php

use FFmphp\Formats\Video\MP4;

class MP4Anime extends MP4
{
    public function build()
    {
        return parent::build()->withOption('-tune', 'animation');
    }
}
```

Here is an another example, this time creating a new `Webm` format from scratch:

```php
<?php

use FFmphp\Formats\OutputFormat;
use FFmphp\Formats\InteractsWithOutput;

class Webm implements OutputFormat
{
    use InteractsWithOutput;

    public function build()
    {
        return $this->withOptions([
            '-vcodec' => 'libvpx-vp9',
            '-acodec' => 'libopus',
        ]);
    }
}
```

To use your output format, simply reference it in `save()`:

```php
->save('output.webm', Webm::class)
```

### Save Multiple Files with One Command

You can add an arbitrary number of outputs with one command. For example:

```php
FFmphp::load($input)
    ->save('output.mp4', MP4::class)
    ->save('output.webm', Webm::class)
    ->save('output-audio.mp3', MP3::class)
    ->save('poster.jpg', Poster::class)
    ->save('preview-5x5.jpg', TileFiveByFive::class)
    ->run();
```

The above command would run FFmpeg once and create 5 files.

### Getting the Command

If you want to see the command that will be run without running it, you can replace the `run()` method with `toCommand()`.

For example:

```php
FFmphp::load($input)
    ->save('output.mp4', MP4::class)
    ->toCommand();
```

Might produce this:

```
ffmpeg -i "/path/to/input.mp4" -acodec aac -vcodec libx264 -b:a 128k -preset slower "output.mp4"
```

### Conditionally Adding Output Streams

You may want to add some output formats only when certain conditions are met. You can use `when()` for those situations. For example, you might want to create a 1080p version of a video _only_ when the source resolution was high enough:

```php
$is_hd = true;

FFmphp::load($input)
    ->save('output.webm', Webm::class)
    ->when($is_hd, function ($command) {
        $command->save('output-1080p.webm', Webm1080p::class);
    })
    ->save('thumbnail.jpg', Thumbnail::class)
    ->run();
```

The second argument of `when()` is a callback function that will only be applied when the first argument is `true`.

### Adding Encoder Options

The third parameter of `save()` accepts an associative array of options that will be added to the output. In some cases this may be more convenient than creating a new class for each output variation.

```php
FFmphp::load($input)
    ->save('output.mp4', MP4::class, [
        '-preset' => 'veryslow',
        '-crf' => '28',
    ])
    ->run();
```

The third argument can also be a function:

```php
FFmphp::load($input)
    ->save('output.mp4', MP4::class, function (OutputBuilder $output) {
        return $output->withOption('-preset', 'veryslow')
                      ->withOption('-crf', '28');
    })
    ->run();
```

Using this feature excessively will make your code harder to read, which is why it is recommended instead to have a class for every type of output.

### The Progress Callback

The `run()` method accepts a function which will be called approximately once a second as long as FFmpeg is running. The function will receive the current time position of the input, formatted like `00:00:00.00`.

For example:

```php
FFmphp::load($input)
    ->save('output.mp4')
    ->run(function ($time) {
        // Logic for notifying users of progress updates
    });
```

### Raw Commands

The `raw()` method is perfect if you need more control, have complicated filters, or already know FFmpeg and just want the convenience of the php integration. The `raw()` method takes an array of arguments to be passed to FFmpeg.

```php
FFmphp::raw([
        '-y',
        '-i',
        'input.mp4',
        'output.mp4',
    ])
    ->run();

```
