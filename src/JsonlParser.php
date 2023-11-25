<?php

namespace Elecena\JsonlParser;

class JsonlParser implements \Countable
{
    const LINES_SEPARATOR = "\n";

    /**
     * @param resource $stream
     */
    public function __construct(protected $stream)
    {
    }

    public function push(array $item): void
    {

    }

    public function pushItems(\Iterator $items): void
    {
        foreach($items as $item) {
            $this->push($item);
        }
    }
    public function pop(): ?array
    {
        return null;
    }

    /**
     * This method returns how many JSON-encoded lines are in the stream.
     *
     * This can be heavy on large files this method rewinds and then reads the entire stream content.
     *
     * @return int
     */
    public function count(): int
    {
        $count = 0;
        fseek($this->stream, 0);

        /**
         * https://www.php.net/manual/en/function.stream-get-line.php
         */
        while(($_line = stream_get_line($this->stream, 1024 * 1024, self::LINES_SEPARATOR)) !== false) {
            $count++;
        }

        return $count;
    }
}
