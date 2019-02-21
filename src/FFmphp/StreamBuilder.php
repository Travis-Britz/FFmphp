<?php

namespace FFmphp;

class StreamBuilder
{
    use HasOptions;

    /**
     * The stream source or destination.
     *
     * @var string
     */
    protected $stream_url;

    /**
     * Get the stream options and source/destination as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return array_merge($this->getOptionsArray(), [$this->stream_url]);
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
