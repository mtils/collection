<?php
/**
 *
 * Created by mtils on 21.04.2024 at 12:01.
 **/

namespace Collection\Test;

use Collection\StringList;
use PHPUnit\Framework\TestCase;

use function iterator_to_array;

class StringListTest extends TestCase
{
    public function testInitialization()
    {
        $list = new StringList(['hello', 'world'], ', ');
        $this->assertEquals('hello, world', (string) $list);
    }

    public function testEmptyInitialization()
    {
        $list = new StringList();
        $this->assertEquals('', (string) $list);
        $this->assertCount(0, $list);
        $this->assertSame([], iterator_to_array($list));
    }

    public function testToString()
    {
        $list = new StringList(['one', 'two', 'three'], ', ');
        $list->prefix = '[';
        $list->suffix = ']';
        $this->assertEquals('[one, two, three]', (string) $list);
    }

    public function testFromString()
    {
        $list = StringList::fromString('apple banana cherry', ' ');
        $this->assertCount(3, $list);
        $this->assertEquals('apple', $list[0]);
        $this->assertEquals('cherry', $list[2]);

        // Test with custom separator and trimming
        $list = StringList::fromString(',apple,banana,cherry,', ',');
        $list->prefix = '$';
        $list->suffix = '%';
        $this->assertEquals('$', $list->prefix);
        $this->assertEquals('%', $list->suffix);
        $this->assertEquals('$apple,banana,cherry%', (string) $list);
    }

    public function testCopy()
    {
        $original = new StringList(['x', 'y'], ', ');
        $original->prefix = '<';
        $original->suffix = '>';
        $copy = $original->copy();

        $this->assertEquals('<x, y>', (string) $copy);
        $this->assertEquals($original->delimiter, $copy->delimiter);
        $this->assertEquals($original->prefix, $copy->prefix);
        $this->assertEquals($original->suffix, $copy->suffix);
    }
}