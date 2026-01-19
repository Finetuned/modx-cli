<?php

namespace MODX\CLI\Tests\Configuration;

use PHPUnit\Framework\TestCase;
use MODX\CLI\Configuration\Extension;

class ExtensionTest extends TestCase
{
    /**
     * @param array $items
     *
     * @dataProvider getData
     */
    public function testConstructor($items)
    {
        $config = new Extension($items, false);

        $this->assertNotEmpty($config->getAll(), 'Classes passed in constructor should be set');
        $this->assertEquals($items, $config->getAll(), 'Classes passed in constructor should match');
    }

    /**
     * @param array $items
     *
     * @dataProvider getData
     */
    public function testGetter($items)
    {
        $config = new Extension($items, false);

        $this->assertEquals('\Another\Command\Class', $config->get('\Another\Command\Class'), 'Getting a valid class name should return its class name');
        $this->assertNull($config->get('\Fake\Class'), 'Getting an invalid class name should return null');
    }

    /**
     * @param array $items
     *
     * @dataProvider getData
     */
    public function testSetter($items)
    {
        $config = new Extension($items, false);

        $config->set('\Another\Command\Class');
        $this->assertEquals($items, $config->getAll(), 'Trying to add an already added class should not change the items');

        $config->remove('\Another\Command\Class');
        $this->assertEquals(1, count($config->getAll()), 'Removing a class name from the items is possible');
        $this->assertNull($config->get('\Another\Command\Class'));

        $class = '\\Some\\Class\\Name';
        $config->set($class);
        $this->assertEquals($class, $config->get($class), 'Adding a new command class is possible');
    }

    /**
     * @param array $items
     *
     * @dataProvider getData
     */
    public function testFormat($items)
    {
        $config = new Extension($items, false);
        $formatted = $config->formatData();
        $formatted = str_replace('<?php', '', $formatted);
        sort($items);

        $this->assertEquals($items, eval($formatted), 'Items are correctly formatted for file storage');
    }

    public function testSave()
    {
        $this->markTestIncomplete('Implement persistence tests for Extension config.');
    }

    public function testLoad()
    {
        $this->markTestIncomplete('Implement persistence tests for Extension config.');
    }

    public static function getData()
    {
        return [
            [
                [
                    '\Some\Command\Class',
                    '\Another\Command\Class'
                ],
            ],
        ];
    }
}
