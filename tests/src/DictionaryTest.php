<?php
/**
 *
 * Created by mtils on 21.04.2024 at 13:15.
 **/

namespace Collection\Test;

use Collection\Dictionary;
use Collection\OrderedList;
use PHPUnit\Framework\TestCase;

class DictionaryTest extends TestCase
{
    public function testBasicOperations()
    {
        $dict = new Dictionary(['a' => 1, 'b' => 2]);

        // Test count
        $this->assertCount(2, $dict);

        // Test ArrayAccess set and get
        $dict['c'] = 3;
        $this->assertEquals(3, $dict['c']);

        // Test unset
        unset($dict['b']);
        $this->assertFalse(isset($dict['b']));
    }

    public function testGetWithDefault()
    {
        $dict = new Dictionary(['a' => 1]);
        $this->assertEquals(1, $dict->get('a'));
        $this->assertEquals('default', $dict->get('nonexistent', 'default'));
    }

    public function testHasAndHasKey()
    {
        $dict = new Dictionary(['a' => 1, 'b' => null]);
        $this->assertTrue($dict->hasKey('a'));
        $this->assertFalse($dict->has('b'));  // 'b' is set but is null/false
        $this->assertTrue($dict->hasKey('b'));
        $this->assertFalse($dict->has('c'));  // 'c' is not set
    }

    public function testClearAndCopy()
    {
        $dict = new Dictionary(['a' => 1, 'b' => 2]);
        $copy = $dict->copy();
        $dict->clear();

        // After clearing, original should be empty
        $this->assertCount(0, $dict);

        // Copy should remain unchanged
        $this->assertCount(2, $copy);
        $this->assertEquals(1, $copy['a']);
        $this->assertEquals(2, $copy['b']);
    }

    public function testWithout()
    {
        $dict = new Dictionary(['a' => 1, 'b' => 2]);
        $newDict = $dict->without('a');

        // Check 'a' is removed in newDict but not in original dict
        $this->assertFalse(isset($newDict['a']));
        $this->assertTrue(isset($dict['a']));
    }

    public function testReturnOrSet()
    {
        $dict = new Dictionary();
        $value = $dict->returnOrSet('a', 100);
        $this->assertEquals(100, $value);
        $this->assertEquals(100, $dict['a']);
    }

    public function testPop()
    {
        $dict = new Dictionary(['a' => 1, 'b' => 2]);
        $value = $dict->pop('b');
        $this->assertEquals(2, $value);
        $this->assertFalse(isset($dict['b']));

        // Test pop with default
        $value = $dict->pop('nonexistent', 'default');
        $this->assertEquals('default', $value);
    }

    public function testItemsKeysValues()
    {
        $dict = new Dictionary(['a' => 1, 'b' => 2]);
        $items = $dict->items();
        $keys = $dict->keys();
        $values = $dict->values();

        // Assert types of returned values
        $this->assertInstanceOf(OrderedList::class, $items);
        $this->assertInstanceOf(OrderedList::class, $keys);
        $this->assertInstanceOf(OrderedList::class, $values);

        // Assert contents
        $this->assertEquals([['a', 1], ['b', 2]], $items->src());
        $this->assertEquals(['a', 'b'], $keys->src());
        $this->assertEquals([1, 2], $values->src());
    }

    public function testIterator()
    {
        $dict = new Dictionary(['a' => 1, 'b' => 2]);
        $values = [];
        foreach ($dict as $key => $value) {
            $values[$key] = $value;
        }
        $this->assertEquals(['a' => 1, 'b' => 2], $values);
    }
}
