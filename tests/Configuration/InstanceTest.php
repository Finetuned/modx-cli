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
        $config = new Instance($items, false);

        $this->assertEquals($items, $config->getAll(), 'Items passed in the constructor should be available using getAll()');
    }

    /**
     * @param array $items
     *
     * @dataProvider getData
     */
    public function testFindFromPath($items)
    {
        $config = new Instance($items, false);

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
        $config = new Instance($items, false);
        $formatted = $config->formatConfigurationData();

        $this->assertEquals($items, parse_ini_string($formatted, true), 'Formatting the items array should result in a valid ini string');
    }

    /**
     * @param array $items
     *
     * @dataProvider getData
     */
    public function testFormatter($items)
    {
        $config = new Instance($items, false);
        $formatted = $config->formatConfigurationData();

        $expected = array();
        foreach ($items as $instanceName => $configData) {
            $expected[$instanceName] = $configData;
        }

        $this->assertEquals($expected, parse_ini_string($formatted, true), 'Formatting the items array should result in a valid ini string');
    }

    public function _testLoad()
    {
        // @TODO
    }

    public function _testSave()
    {
        // @TODO
    }

    public static function getData()
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
