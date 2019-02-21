<?php

namespace FFmphp;

trait HasOptions
{

    /**
     * A list of options.
     *
     * @var array
     */
    protected $options = [];

    /**
     * Add an array of options.
     *
     * For options that do not have a value (such as "-y"), use a boolean value "true" to add the option
     * or "false" to remove it.
     *
     * @param array $options
     *
     * @return $this
     */
    public function withOptions(array $options)
    {
        $builder = $this;
        foreach ($options as $option => $value) {
            $builder = $builder->withOption($option, $value);
        }
        return $builder;
    }

    /**
     * Add an option.
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
     * Get the options as an array.
     *
     * @return array
     */
    public function getOptionsArray()
    {
        $command = [];
        foreach ($this->options as $key => $value) {
            if ($value !== false) {
                $command[] = $key;
            }
            if (!is_bool($value)) {
                $command[] = $value;
            }
        }

        return $command;
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
}