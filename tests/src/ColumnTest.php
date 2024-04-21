<?php
/**
 *
 * Created by mtils on 21.04.2024 at 13:31.
 **/

namespace Collection\Test;

use PHPUnit\Framework\TestCase;
use Collection\Column;
use UnexpectedValueException;

use function var_dump;

class ColumnTest extends TestCase
{
    public function testSetAndGetAccessor()
    {
        $column = new Column();
        $column->setAccessor('name');
        $this->assertEquals('name', $column->getAccessor());
    }

    public function testMethodAccessorExtraction()
    {
        $column = new Column();
        $column->setAccessor('getName()');
        $this->assertEquals('getName', $column->getAccessor());
        $this->assertTrue($column->needsMethodAccess());
    }

    public function testGetTitleDefaultsToName()
    {
        $column = new Column();
        $column->setAccessor('tom');
        $this->assertEquals('tom', $column->getTitle());
    }

    public function testCustomTitle()
    {
        $column = new Column();
        $column->setAccessor('name');
        $column->setTitle('Custom Name');
        $this->assertEquals('Custom Name', $column->getTitle());
    }

    public function testGetValueFromObject()
    {
        $object = new class {
            public $name = 'Example';
            public function getName() {
                return 'Method Example';
            }
        };
        $column = new Column();
        $column->setSrc($object);
        $column->setAccessor('name');
        $this->assertEquals('Example', $column->getValue());

        // Test method access
        $column->setAccessor('getName()');
        $this->assertEquals('Method Example', $column->getValue());
    }

    public function testGetValueFromArray()
    {
        $array = ['name' => 'Example'];
        $column = new Column();
        $column->setSrc($array);
        $column->setAccessor('name');
        $this->assertEquals('Example', $column->getValue());
    }

    public function testGetValueWithCallable()
    {
        $object = new class {
            public $name = 'Example';
        };
        $column = new Column();
        $column->setSrc($object);
        $column->setAccessor('name', function($col, $src, $accessor) {
            return strtoupper($src->$accessor);
        });
        $this->assertEquals('EXAMPLE', $column->getValue());
    }

    public function testGetNonExistentPropertyThrowsException()
    {
        $this->expectException(UnexpectedValueException::class);
        $column = new Column();
        $column->setSrc(123); // Invalid source for the operations
        $column->setAccessor('name');
        $column->getValue();
    }
}

