<?php namespace MODX\CLI\Tests\Configuration;

use PHPUnit\Framework\TestCase;
use MODX\CLI\Configuration\Instance;

class InstanceTest extends TestCase
{
    /**
     * @param array $items
     *
     * @dataProvider getData
     */
    public function testConstructor($items)
    {
        $config = new Instance($items);

        $this->assertEquals($items, $config->getAll(), 'Items passed in the constructor should be available using getAll()');
    }

    /**
     * @param array $items
     *
     * @dataProvider getData
     */
    public function testFindFromPath($items)
    {
        $config = new Instance($items);

        $this->assertEquals('InstanceName', $config->findFormPath('./src/'), 'We are able to find an instance name from a given path.');
        $this->assertEquals('InstanceName', $config->findFormPath('./src'), 'We are able to find an instance name from a given path minus its trailing slash.');
        $this->assertEquals('InstanceName', $config->findFormPath('./src/Configuration/'), 'We are able to find an instance name from a given path nested in base_path.');
        $this->assertNull($config->findFormPath('/not/registered/path/'), 'Searching for a not registered path should return null');

        $this->assertEquals('CurrentInstanceName', $config->current(), 'We can find the current instance using current() method');
    }

    /**
     * @param array $items
     *
     * @dataProvider getData
     */
    public function testCurrentConfig($items)
    {
        $config = new Instance($items);

        $this->assertEquals($items['CurrentInstanceName'], $config->getCurrentConfig(), 'We can get the full current instance configuration using getCurrentConfig');
        $this->assertEquals($items['CurrentInstanceName']['base_path'], $config->getCurrentConfig('base_path'), 'We can get a single configuration item/index from the current instance using getCurrentConfig');
    }

    /**
     * @param array $items
     *
     * @dataProvider getData
     */
    public function testFormatter($items)
    {
        $config = new Instance($items);
        $reflection = new \ReflectionClass($config);
        $method = $reflection->getMethod('formatConfigurationData');
        $method->setAccessible(true);

        $this->assertEquals($items, parse_ini_string($method->invoke($config), true), 'Formatting the items array should result in a valid ini string');
    }

    public function _testLoad()
    {
        // @TODO
    }

    public function _testSave()
    {
        // @TODO
    }

    public function getData()
    {
        return array(
            array(
                array(
                    'InstanceName' => array(
                        'base_path' => './src/',
                    ),
                    'CurrentInstanceName' => array(
                        'base_path' => getcwd(),
                    ),
                )
            ),
        );
    }
}
