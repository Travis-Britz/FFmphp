<?php

namespace FFmphp;

class StreamBuilder
{

    /**
     * The options for the input/output stream.
     *
     * @var array
     */
    protected $options = [];

    /**
     * The stream source or destination.
     *
     * @var string
     */
    protected $stream_url;

    /**
     * Add an array of options for the input/output stream.
     *
     * For options that do not have a value (such as "-y"), use a boolean value "true" to add the option
     * or "false" to remove it.
     *
     * @param array $options
     *
     * @return $this
     */
    public function withOptions($options)
    {
        $builder = $this;
        foreach ($options as $option => $value) {
            $builder = $builder->withOption($option, $value);
        }
        return $builder;
    }

    /**
     * Add an option to the input/output stream.
     *
     * Use a boolean value of "false" to remove an option, or "true" if it does not
     * accept a value.
     *
     * @param             string $option
     * @param bool|string        $value
     *
     * @return $this
     */
    public function withOption($option, $value = true)
    {
        $this->options[$option] = $value;
        return $this;
    }

    /**
     * Conditionally chain method calls onto the current builder instance.
     *
     * @param bool     $condition
     * @param Callable $callback The function called when $condition is true. It will receive the current
     *                           builder instance as its first argument.
     *
     * @return $this
     */
    public function when($condition, Callable $callback)
    {
        if ($condition) {
            $callback($this);
        }

        return $this;
    }

    /**
     * Get the stream options and source/destination as an array.
     *
     * @return array
     */
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

    /**
     * Set the source or destination of the stream.
     *
     * @param string $stream_url
     *
     * @return $this
     */
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
