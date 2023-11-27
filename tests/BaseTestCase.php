<?php

use PHPUnit\Framework\TestCase;

abstract class BaseTestCase extends TestCase
{
    /**
     * Returns a readable, in-memory stream with the provided string as a content
     *
     * @return resource
     */
    protected static function streamFromString(string $string)
    {
        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $string);
        rewind($stream);

        return $stream;
    }
}
