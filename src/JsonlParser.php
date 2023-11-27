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

    public function push(array|string $item): void
    {
        $encoded = json_encode($item);
        fwrite($this->stream, $encoded . self::LINES_SEPARATOR);
    }

    public function pushItems(\Iterator $items): void
    {
        foreach($items as $item) {
            $this->push($item);
        }
    }

    /**
     * This method returns the last item from the file and removes it.
     */
    public function pop(): null|array|string
    {
        /***
         * Rewind to the end of the file and try to find the last newline
         *
         * @see https://www.php.net/manual/en/function.fseek.php
         */
        fseek($this->stream, 0, SEEK_END);

        // this stream is now empty
        if (ftell($this->stream) === 0) {
            return null;
        }

        // start reading from the end of the stream in reverse order, byte by byte
        fseek($this->stream, -1, SEEK_END);
        $buffer = fread($this->stream, 1);

        while(ftell($this->stream) > 1) {
            // move two bytes back (one already read and the one before it)
            fseek($this->stream, -2, SEEK_CUR);

            $char = fread($this->stream, 1);
            $buffer .= $char;

            if ($char === self::LINES_SEPARATOR) {
                break;
            }

            if (ftell($this->stream) === 0) {
                break;
            }
        }

        $buffer = strrev($buffer);

        // truncate the stream and remove the trailing newline
        $pos = ftell($this->stream);
        ftruncate($this->stream, $pos < 1 ? 0 : $pos-1);

        return json_decode($buffer, associative: true);
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
        rewind($this->stream);

        /**
         * https://www.php.net/manual/en/function.stream-get-line.php
         */
        while(stream_get_line($this->stream, 1024 * 1024, self::LINES_SEPARATOR) !== false) {
            $count++;
        }

        return $count;
    }
}
