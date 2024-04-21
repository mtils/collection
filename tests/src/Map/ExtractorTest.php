<?php
/**
 *
 * Created by mtils on 21.04.2024 at 16:05.
 **/

namespace Collection\Test\Map;


use Collection\Map\Extractor;
use DomainException;
use PHPUnit\Framework\TestCase;
use stdClass;

class ExtractorTest extends TestCase
{
    public function testInvokeWithObjectProperties()
    {
        $item = new stdClass();
        $item->id = 1;
        $item->name = "Test Item";

        $extractor = new Extractor('id', 'name');
        $result = $extractor('foo', $item);

        $this->assertEquals([1, "Test Item"], $result);
    }

    public function testInvokeWithObjectMethods()
    {
        $item = new class {
            public function getId()
            {
                return 1;
            }

            public function getName()
            {
                return "Test Item";
            }
        };

        $extractor = new Extractor('getId()', 'getName()');
        $result = $extractor('', $item);

        $this->assertEquals([1, "Test Item"], $result);
    }

    public function testInvokeWithArray()
    {
        $item = ['id' => 1, 'name' => 'Test Item'];

        $extractor = new Extractor('id', 'name');
        $result = $extractor('', $item);

        $this->assertEquals([1, "Test Item"], $result);
    }

    public function testSpecialAccessors()
    {
        $item = ['name' => 'Test Item'];

        $extractor = new Extractor(Extractor::KEY, Extractor::VALUE);
        $result = $extractor('name', $item['name'], 0);

        $this->assertEquals(['name', 'Test Item'], $result);
    }

    public function testSetItemValue()
    {
        $item = new stdClass();
        $item->name = "Old Name";

        $extractor = new Extractor('name');
        $extractor->setItemValue($item, "New Name");

        $this->assertEquals("New Name", $item->name);
    }

    public function testSetItemValueThrowsExceptionForMethods()
    {
        $this->expectException(DomainException::class);

        $item = new stdClass();
        $extractor = new Extractor('getName()');
        $extractor->setItemValue($item, "New Name");
    }

    public function testUnsetItemKey()
    {
        $item = ['name' => 'Test Name'];

        $extractor = new Extractor('name');
        $extractor->unSetItemKey($item);

        $this->assertArrayNotHasKey('name', $item);
    }

    public function testUnsetItemKeyWithParameter()
    {
        $item = ['name' => 'Test Name'];

        $extractor = new Extractor('name');
        $extractor->unSetItemKey($item, 'name');

        $this->assertArrayNotHasKey('name', $item);
    }

    public function testUnsetItemKeyThrowsExceptionForMethods()
    {
        $this->expectException(DomainException::class);

        $item = new stdClass();
        $item->name = "Test Name";

        $extractor = new Extractor('getName()');
        $extractor->unSetItemKey($item, 'name');
    }
}
