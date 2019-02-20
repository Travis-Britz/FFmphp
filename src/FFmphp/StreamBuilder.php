<?php

namespace FFmphp;

class StreamBuilder
{

    protected $options = [];

    protected $stream_url;

    public function withOptions($options)
    {
        $builder = $this;
        foreach ($options as $option => $value) {
            $builder = $builder->withOption($option, $value);
        }
        return $builder;
    }

    /**
     * @param $option
     * @param bool|string $value
     * @return $this
     */
    public function withOption($option, $value = true)
    {
        $this->options[$option] = $value;
        return $this;
    }

    /**
     * @param bool $condition
     * @param Callable $callback
     * @return \FFmphp\StreamBuilder
     */
    public function when($condition, Callable $callback)
    {
        if ($condition) {
            $callback($this);
        }

        return $this;
    }

    public function toArray()
    {
        $options = [];

        foreach ($this->options as $option => $value) {
            if ($value !== false) {
                $options[] = $option;
            }
            if ($value !== true) {
                $options[] = $value;
            }
        }

        $options[] = $this->stream_url;

        return $options;
    }

    public function url($stream_url)
    {
        if ('\\' == \DIRECTORY_SEPARATOR) {
            $this->stream_url = preg_replace('/^\/dev\/null$/', 'NUL', $stream_url);
        } else {
            $this->stream_url = $stream_url;
        }

        return $this;
    }
}
