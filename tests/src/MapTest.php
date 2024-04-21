<?php
/**
 *
 * Created by mtils on 21.04.2024 at 12:23.
 **/

namespace Collection\Test;

use Collection\Map;
use PHPUnit\Framework\TestCase;
use stdClass;

class MapTest extends TestCase
{
    public function testCanInstantiateWithCallable()
    {
        $src = ['a' => 'apple', 'b' => 'banana'];
        $extractor = function($key, $value, $index) {
            return [$key, strtoupper($value)];
        };

        $map = new Map($src, $extractor);
        $this->assertEquals('APPLE', $map['a']);
        $this->assertEquals('BANANA', $map['b']);
    }

    public function testOffsetExists()
    {
        $src = [1, 2, 3];
        $extractor = function($key, $value, $index) {
            return [$index, $value * 2];
        };
        $map = new Map($src, $extractor);
        $this->assertTrue(isset($map[0]));
        $this->assertFalse(isset($map[4]));
    }

    public function testOffsetGet()
    {
        $src = [1, 2, 3];
        $extractor = function($key, $value, $index) {
            return [$index, $value * 2];
        };
        $map = new Map($src, $extractor);
        $this->assertEquals(6, $map[2]);
    }

    public function testOffsetSet()
    {
        $src = [new \stdClass()];
        $extractor = new class {
            public function __invoke($key, $value, $index) {
                return [$index, $value->prop ?? null];
            }
            public function setItemValue($object, $value) {
                $object->prop = $value;
            }
        };
        $map = new Map($src, $extractor);
        $map[0] = 'test';
        $this->assertEquals('test', $map[0]);
    }

    public function testOffsetUnset()
    {
        $src = [new \stdClass()];
        $extractor = new class {
            public function __invoke($key, $value, $index) {
                return [$index, $value instanceof stdClass ? $value : null];
            }
            public function setItemValue($object, $value) {
                $object->prop = $value;
            }
            public function unsetItemKey($object, $key) {
                unset($object->prop);
            }
        };
        $map = new Map($src, $extractor);
        $map[0]->prop = 'test';
        unset($map[0]);
        $this->assertFalse($map[0] === 'test');
    }

    public function testCount()
    {
        $src = [1, 2, 3];
        $map = new Map($src, function($key, $value, $index) {
            return [$index, $value];
        });
        $this->assertCount(3, $map);
    }

    public function testGetIterator()
    {
        $src = ['a' => 1, 'b' => 2];
        $extractor = function($key, $value, $index) {
            return [$key, $value * 10];
        };
        $map = new Map($src, $extractor);
        $iterator = $map->getIterator();

        $results = [];
        foreach ($iterator as $key => $value) {
            $results[$key] = $value;
        }
        $this->assertEquals(['a' => 10, 'b' => 20], $results);
    }
}