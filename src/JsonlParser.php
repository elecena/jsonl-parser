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
    public function pop(): ?array
    {
        return null;
    }

    public function count(): int
    {
        return 0;
    }
}
