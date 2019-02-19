<?php

namespace FFmphp\Formats;

/**
 * Trait InteractsWithOutput
 *
 * @method \FFmphp\Formats\OutputBuilder withOption($option, $value = true)
 * @method \FFmphp\Formats\OutputBuilder withOptions(array $options)
 *
 */
trait InteractsWithOutput
{
    public function __call($method, $parameters)
    {
        return (new OutputBuilder)->$method(...$parameters);
    }
}