<?php

namespace MODX\CLI\Tests\Configuration;

use PHPUnit\Framework\TestCase;

class BaseTest extends TestCase
{
    public function getMocked()
    {
        return $this->getMockForAbstractClass('MODX\CLI\Configuration\Base');
    }

    /**
     * @param string $key
     * @param mixed $value
     *
     * @dataProvider getData
     */
    public function testSetterAndGetter($key, $value)
    {
        $mock = $this->getMocked();
        $mock->set($key, $value);

        $this->assertEquals($value, $mock->get($key), 'Retrieving item is possible');
    }

    /**
     * @param string $key
     * @param mixed $value
     *
     * @dataProvider getData
     */
    public function testRemove($key, $value)
    {
        $mock = $this->getMocked();
        $mock->set($key, $value);
        $mock->remove($key);

        $this->assertNull($mock->get($key), 'It is possible to remove an item');
    }

    public static function getData()
    {
        return [
            ['key', 'value'],
            ['array', ['a' => 'b', 'c' => 'd']],
        ];
    }

    /**
     * Helper method to access protected or private properties via reflection.
     *
     * @param object $object The object containing the property.
     * @param string $propertyName The name of the property to access.
     * @return mixed The value of the property.
     */
    protected function getProtectedProperty($object, $propertyName)
    {
        $reflection = new \ReflectionClass($object);
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);

        return $property->getValue($object);
    }
}
