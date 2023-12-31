<?php

use \Elecena\JsonlParser\JsonlParser;

class JsonParserTest extends BaseTestCase
{
    const ITEM = ['foo' => 'bar', 'ok' => true];
    const ITEM_ONE = ['foo' => 'bar', 'ok' => true];
    const ITEM_TWO = ['foo' => 'test', 'ok' => true];

    public function testOpensAnEmptyString(): void
    {
        $stream = self::streamFromString('');
        $parser = new JsonlParser($stream);
        $this->assertNull($parser->pop());
        $this->assertCount(0, $parser);
    }

    public function testOpensASingleLine(): void
    {
        $stream = self::streamFromString(json_encode(self::ITEM));
        $parser = new JsonlParser($stream);
        $this->assertCount(1, $parser);
        $this->assertSame(self::ITEM, $parser->pop());
        $this->assertCount(0, $parser);
    }
    public function testOpensASingleLineWithTrailingNewLine(): void
    {
        $stream = self::streamFromString(json_encode(self::ITEM) . JsonlParser::LINES_SEPARATOR);
        $parser = new JsonlParser($stream);
        $this->assertCount(1, $parser);
        $this->assertSame(self::ITEM, $parser->pop());
        $this->assertCount(0, $parser);
    }
    public function testOpensTwoLines(): void
    {
        $stream = self::streamFromString(json_encode(self::ITEM_ONE) . JsonlParser::LINES_SEPARATOR . json_encode(self::ITEM_TWO));
        $parser = new JsonlParser($stream);
        $this->assertCount(2, $parser);
        $this->assertSame(self::ITEM_TWO, $parser->pop());
        $this->assertCount(1, $parser);
        $this->assertSame(self::ITEM_ONE, $parser->pop());
        $this->assertCount(0, $parser);
    }

    public function testOpensAnEmptyStringAndAddsAnItem(): void
    {
        $stream = self::streamFromString('');
        $parser = new JsonlParser($stream);
        $this->assertCount(0, $parser);
        $parser->push(self::ITEM);
        $this->assertCount(1, $parser);
        $this->assertSame(self::ITEM, $parser->pop());
        $this->assertCount(0, $parser);
    }

    public function testHandlesStrings(): void
    {
        $item = 'https://foo.bar.net';

        $stream = self::streamFromString('');
        $parser = new JsonlParser($stream);
        $this->assertCount(0, $parser);
        $parser->push($item);
        $this->assertCount(1, $parser);
        $this->assertSame($item, $parser->pop());
        $this->assertCount(0, $parser);
        $this->assertNull($parser->pop());
    }

    public function testIterator(): void
    {
        $stream = self::streamFromString('');
        $parser = new JsonlParser($stream);

        $parser->push('one');
        $parser->push('two');
        $parser->push('three');
        $this->assertCount(3, $parser);

        $list = iterator_to_array($parser->iterate());

        $this->assertCount(0, $parser);
        $this->assertCount(3, $list);
        $this->assertSame(['three', 'two', 'one'], $list);
    }

    public function testPushItems(): void
    {
        $stream = self::streamFromString('');
        $parser = new JsonlParser($stream);

        $parser->pushItems(items:self::iterator());
        $this->assertCount(3, $parser);

        $list = iterator_to_array($parser->iterate());

        $this->assertCount(0, $parser);
        $this->assertCount(3, $list);
        $this->assertSame(['three', 'two', 'one'], $list);
    }

    public function testOpensAnEmptyFile(): void
    {
        $stream = tmpfile();
        $parser = new JsonlParser($stream);
        $this->assertNull($parser->pop());
        $this->assertCount(0, $parser);

        fclose($stream); // this removes the file
    }
    public function testPushItemsToFile(): void
    {
        $stream = tmpfile();
        $parser = new JsonlParser($stream);
        $this->assertCount(0, $parser);

        $parser->pushItems(items:self::iterator());
        $this->assertCount(3, $parser);

        $list = iterator_to_array($parser->iterate());

        $this->assertCount(0, $parser);
        $this->assertCount(3, $list);
        $this->assertSame(['three', 'two', 'one'], $list);

        fclose($stream); // this removes the file
    }

    public function testFromFile(): void
    {
        $tmpFilename = tempnam(directory: sys_get_temp_dir(), prefix: 'jsonld');

        $parser = JsonlParser::fromFile($tmpFilename);
        $this->assertCount(0, $parser);
        $this->assertTrue($parser->empty());

        $parser->push(self::ITEM_ONE);
        $parser->push(self::ITEM_TWO);
        $this->assertCount(2, $parser);
        $this->assertFalse($parser->empty());

        // now get one and push the next item
        $this->assertSame(self::ITEM_TWO, $parser->pop());
        $this->assertStringEqualsFile(
            expectedFile: $tmpFilename,
            actualString: json_encode(self::ITEM_ONE) . JsonlParser::LINES_SEPARATOR
        );
        $this->assertCount(1, $parser);
        $parser->push(self::ITEM);
        $this->assertStringEqualsFile(
            expectedFile: $tmpFilename,
            actualString: json_encode(self::ITEM_ONE) . JsonlParser::LINES_SEPARATOR . json_encode(self::ITEM) . JsonlParser::LINES_SEPARATOR
        );
        $this->assertCount(2, $parser);

        // now, empty the file
        $parser->pop();
        $parser->pop();
        $this->assertCount(0, $parser);
        $this->assertTrue($parser->empty());
        $this->assertStringEqualsFile(
            expectedFile: $tmpFilename,
            actualString: ''
        );

        unlink($tmpFilename);
    }
}
