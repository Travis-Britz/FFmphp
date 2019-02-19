<?php

namespace FFmphp\Formats;

class OutputBuilder
{

    protected $options = [];

    protected $destination;

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

    public function destination($destination)
    {
        if ('\\' == \DIRECTORY_SEPARATOR) {
            $this->destination = preg_replace('/^\/dev\/null$/', 'NUL', $destination);
        } else {
            $this->destination = $destination;
        }

        return $this;
    }

    public function toArray()
    {
        $options = [];
        foreach ($this->options as $option => $value) {
            $options[] = $option;
            if ($value !== true) {
                $options[] = $value;
            }
        }
        $options[] = $this->destination;

        return $options;
    }

}
