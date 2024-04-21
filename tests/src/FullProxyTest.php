<?php
/**
 *
 * Created by mtils on 21.04.2024 at 12:50.
 **/

namespace Collection\Test;

use ArrayObject;
use Collection\FullProxy;
use Error;
use PHPUnit\Framework\TestCase;

class FullProxyTest extends TestCase
{
    protected $object;

    protected function setUp(): void
    {
        $this->object = new class extends ArrayObject
        {
            public $property = 'value';
            public function __construct()
            {
                parent::__construct(['one' => 1, 'two' => 2]);
            }

            public function method() : string
            {
                return 'called';
            }

            public function all() {
                return parent::getArrayCopy();
            }
        };

    }

    public function testPropertyAccess()
    {
        $proxy = new FullProxy($this->object);
        $this->assertEquals('value', $proxy->property);

        $proxy->newProperty = 'newValue';
        $this->assertEquals('newValue', $this->object->newProperty);
        $this->assertTrue(isset($proxy->property));
        $this->assertFalse(isset($proxy->undefinedProperty));

        unset($proxy->property);
        $this->assertFalse(isset($this->object->property));
    }

    public function testMethodCall()
    {
        $proxy = new FullProxy($this->object);
        $this->assertEquals('called', $proxy->method());
    }

    public function testArrayAccess()
    {
        $proxy = new FullProxy($this->object);
        $this->assertTrue(isset($proxy['one']));
        $this->assertEquals(1, $proxy['one']);

        $proxy['three'] = 3;
        $this->assertEquals(3, $this->object['three']);
        $this->assertTrue(isset($proxy['three']));

        unset($proxy['two']);
        $this->assertFalse(isset($this->object['two']));
    }

    public function testInvoke()
    {
        $callableObject = new class {
            public function __invoke($param) {
                return $param * 2;
            }
        };

        $proxy = new FullProxy($callableObject);
        $this->assertEquals(4, $proxy(2));
    }

    public function testToString()
    {
        $stringableObject = new class {
            public function __toString() {
                return 'stringable';
            }
        };

        $proxy = new FullProxy($stringableObject);
        $this->assertEquals('stringable', (string) $proxy);
    }

    public function testStaticCall()
    {
        $this->expectException(Error::class);
        FullProxy::__callStatic('nonExistentMethod', []);
    }
}
