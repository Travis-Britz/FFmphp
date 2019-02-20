<?php

namespace FFmphp\Formats;

use FFmphp\StreamBuilder;

/**
 * Trait InteractsWithOutput
 *
 * @method \FFmphp\StreamBuilder withOption($option, $value = true)
 * @method \FFmphp\StreamBuilder withOptions(array $options)
 *
 */
trait InteractsWithOutput
{
    public function __call($method, $parameters)
    {
        return (new StreamBuilder)->$method(...$parameters);
    }
}