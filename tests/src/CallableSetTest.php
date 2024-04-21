<?php
/**
 *
 * Created by mtils on 21.04.2024 at 14:06.
 **/

namespace Collection\Test;

use Collection\CallableSet;
use OutOfBoundsException;
use PHPUnit\Framework\TestCase;

class CallableSetTest extends TestCase
{
    protected ?CallableSet $callableSet;

    protected function setUp(): void
    {
        $this->callableSet = new CallableSet();
    }

    public function testAddAndCount()
    {
        $callable1 = function () { return "Hello"; };
        $callable2 = function () { return "World"; };

        $this->callableSet->add($callable1);
        $this->callableSet->add($callable2);

        $this->assertCount(2, $this->callableSet);
        $this->assertTrue($this->callableSet->contains($callable1));
        $this->assertTrue($this->callableSet->contains($callable2));
    }

    public function testPreventDuplicate()
    {
        $callable = function () { return "Hello"; };
        $this->callableSet->add($callable);
        $this->callableSet->add($callable); // Try to add again

        $this->assertCount(1, $this->callableSet);
    }

    public function testExtendWithMultipleCallables()
    {
        $callable1 = function () { return "Hello"; };
        $callable2 = function () { return "World"; };
        $callables = [$callable1, $callable2];

        $this->callableSet->extend($callables);
        $this->assertCount(2, $this->callableSet);
    }

    public function testRemoveCallable()
    {
        $callable = function () { return "Hello"; };
        $this->callableSet->add($callable);
        $this->callableSet->remove($callable);

        $this->assertCount(0, $this->callableSet);
        $this->assertFalse($this->callableSet->contains($callable));
    }

    public function testIndexAccess()
    {
        $callable = function () { return "Hello"; };
        $this->callableSet->add($callable);

        $this->assertEquals($callable, $this->callableSet[0]);

        unset($this->callableSet[0]);
        $this->assertFalse(isset($this->callableSet[0]));
    }

    public function testIterator()
    {
        $callable1 = function () { echo "Hello"; };
        $callable2 = function () { echo "World"; };
        $this->callableSet->add($callable1);
        $this->callableSet->add($callable2);

        $output = '';
        foreach ($this->callableSet as $callable) {
            ob_start();
            $callable();
            $output .= ob_get_clean();
        }

        $this->assertEquals("HelloWorld", $output);
    }

    public function testPop()
    {
        $callable1 = function () { return "Hello"; };
        $callable2 = function () { return "World"; };
        $this->callableSet->add($callable1);
        $this->callableSet->add($callable2);

        $this->callableSet->pop();  // Removes the last element
        $this->assertFalse($this->callableSet->contains($callable2));
        $this->assertTrue($this->callableSet->contains($callable1));

        $this->callableSet->pop(0); // Removes by index
        $this->assertFalse($this->callableSet->contains($callable1));
    }

    public function testCallableNotFoundException()
    {
        $this->expectException(OutOfBoundsException::class);
        $callable = function () { return "Hello"; };
        $this->callableSet->indexOf($callable);  // Should throw an exception
    }
}
