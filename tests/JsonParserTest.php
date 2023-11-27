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

    public function testOpensASingleLine(): void
    {
        $item = ['foo' => 'bar', 'ok' => true];
        
        $stream = self::streamFromString(json_encode($item));
        $parser = new JsonlParser($stream);
        $this->assertCount(1, $parser);
        $this->assertSame($item, $parser->pop());
        $this->assertCount(0, $parser);
    }
    public function testOpensASingleLineWithTrailingNewLine(): void
    {
        $item = ['foo' => 'bar', 'ok' => true];

        $stream = self::streamFromString(json_encode($item) . JsonlParser::LINES_SEPARATOR);
        $parser = new JsonlParser($stream);
        $this->assertCount(1, $parser);
        $this->assertSame($item, $parser->pop());
        $this->assertCount(0, $parser);
    }
    public function testOpensTwoLines(): void
    {
        $itemA = ['foo' => 'bar', 'ok' => true];
        $itemB = ['foo' => 'test', 'ok' => true];

        $stream = self::streamFromString(json_encode($itemA) . JsonlParser::LINES_SEPARATOR . json_encode($itemB));
        $parser = new JsonlParser($stream);
        $this->assertCount(2, $parser);
        $this->assertSame($itemB, $parser->pop());
        $this->assertCount(1, $parser);
        $this->assertSame($itemA, $parser->pop());
        $this->assertCount(0, $parser);
    }
}
