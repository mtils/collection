<?php
/**
 *
 * Created by mtils on 21.04.2024 at 11:38.
 **/

namespace Collection\Test;
use Collection\OrderedList;
use PHPUnit\Framework\TestCase;

class OrderedListTest extends TestCase
{
    public function testEmptyInitialization()
    {
        $list = new OrderedList();
        $this->assertCount(0, $list);
    }

    public function testInitializationWithArray()
    {
        $list = new OrderedList([1, 2, 3]);
        $this->assertCount(3, $list);
        $this->assertEquals(1, $list->first());
        $this->assertEquals(3, $list->last());
    }

    public function testAppendAndPush()
    {
        $list = new OrderedList();
        $list->append(1);
        $list->push(2);
        $this->assertEquals([1, 2], $list->src());
    }

    public function testExtend()
    {
        $list = new OrderedList();
        $list->extend([1, 2, 3]);
        $this->assertCount(3, $list);
    }

    public function testInsert()
    {
        $list = new OrderedList([1, 3]);
        $list->insert(1, 2);
        $this->assertEquals([1, 2, 3], $list->src());
    }

    public function testRemove()
    {
        $list = new OrderedList([1, 2, 3]);
        $list->remove(2);
        $this->assertEquals([1, 3], $list->src());
    }

    public function testPop()
    {
        $list = new OrderedList([1, 2, 3]);
        $list->pop();
        $this->assertEquals([1, 2], $list->src());

        $list->pop(0);
        $this->assertEquals([2], $list->src());
    }

    public function testIndexOf()
    {
        $list = new OrderedList(['a', 'b', 'c']);
        $this->assertEquals(1, $list->indexOf('b'));
    }

    public function testContains()
    {
        $list = new OrderedList(['a', 'b', 'c']);
        $this->assertTrue($list->contains('a'));
        $this->assertFalse($list->contains('x'));
    }

    public function testSortAndReverse()
    {
        $list = new OrderedList([3, 1, 2]);
        $list->sort();
        $this->assertEquals([1, 2, 3], $list->src());

        $list->reverse();
        $this->assertEquals([3, 2, 1], $list->src());
    }

    public function testArrayAccess()
    {
        $list = new OrderedList();
        $list->append('a');
        $list->push('b');
        $this->assertTrue(isset($list[0]));
        $this->assertEquals('a', $list[0]);
        unset($list[0]);
        $this->assertTrue(isset($list[0]));
        $this->assertFalse(isset($list[1]));
        $this->assertEquals('b', $list[0]);
        unset($list[0]);
        $this->assertFalse(isset($list[0]));
        $this->assertCount(0, $list);
    }

    public function testCopy()
    {
        $list = new OrderedList([1, 2, 3]);
        $copy = $list->copy();
        $this->assertEquals($list->src(), $copy->src());
    }
}