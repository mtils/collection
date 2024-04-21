<?php
/**
 *
 * Created by mtils on 21.04.2024 at 15:48.
 **/

namespace Collection\Test\Iterator;


use ArrayObject;
use Collection\Iterator\CastableIterator;
use DomainException;
use PHPUnit\Framework\TestCase;

class CastableIteratorTest extends TestCase
{
    public function testIterateOverArray()
    {
        $src = [1, 2, 3];
        $caster = function ($key, $value) {
            return [$key, $value * 2];
        };
        $iterator = new CastableIterator($src, $caster);

        $result = [];
        foreach ($iterator as $key => $value) {
            $result[$key] = $value;
        }

        $this->assertEquals([0 => 2, 1 => 4, 2 => 6], $result);
    }

    public function testIterateOverIteratorAggregate()
    {
        $src = new ArrayObject([1, 2, 3]);
        $caster = function ($key, $value) {
            return [$key, $value * 2];
        };
        $iterator = new CastableIterator($src, $caster);

        $result = [];
        foreach ($iterator as $key => $value) {
            $result[$key] = $value;
        }

        $this->assertEquals([0 => 2, 1 => 4, 2 => 6], $result);
    }

    public function testCountableInterface()
    {
        $src = [1, 2, 3, 4, 5];
        $caster = function ($key, $value) {
            return [$key, $value];
        };
        $iterator = new CastableIterator($src, $caster);

        $this->assertCount(5, $iterator);
    }

    public function testRewindAndNext()
    {
        $src = [1, 2];
        $caster = function ($key, $value) {
            return [$key, $value * 10];
        };
        $iterator = new CastableIterator($src, $caster);

        $iterator->rewind();
        $this->assertEquals(10, $iterator->current());
        $iterator->next();
        $this->assertEquals(20, $iterator->current());
    }

    public function testValid()
    {
        $src = [1];
        $caster = function ($key, $value) {
            return [$key, $value * 10];
        };
        $iterator = new CastableIterator($src, $caster);

        $iterator->rewind();
        $this->assertTrue($iterator->valid());
        $iterator->next();
        $this->assertFalse($iterator->valid());
    }

    public function testToArray()
    {
        $src = [1, 2, 3];
        $caster = function ($key, $value) {
            return [$key, $value * 2];
        };
        $iterator = new CastableIterator($src, $caster);

        $expectedArray = [0 => 2, 1 => 4, 2 => 6];
        $this->assertEquals($expectedArray, $iterator->toArray());
    }

    public function testExceptionForInvalidSrc()
    {
        $this->expectException(DomainException::class);
        new CastableIterator('invalid', function ($key, $value) {
        });
    }

    public function testExceptionForInvalidCaster()
    {
        $this->expectException(DomainException::class);
        new CastableIterator([1, 2, 3], null);
    }
}
