<?php

use \Elecena\JsonlParser\JsonlParser;

class JsonParserTest extends BaseTestCase
{
    public function testOpensAnEmptyString(): void
    {
        $stream = self::streamFromString('');
        $parser = new JsonlParser($stream);
        $this->assertNull($parser->pop());
        $this->assertCount(0, $parser);
    }
}
