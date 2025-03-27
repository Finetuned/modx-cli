<?php namespace MODX\CLI\Tests\Configuration;

use PHPUnit\Framework\TestCase;
use MODX\CLI\Configuration\Component;
use MODX\Revolution\modSystemSetting;

if (!defined('MODX_CORE_PATH')) {
    // Use the CLI command to get the default MODX_CORE_PATH
    $output = shell_exec('bin/modx config:get-default');

    if (preg_match('/Base path:\s*(.+)/', $output, $matches)) {
        $basePath = trim($matches[1]);
        $output = $basePath . 'core/';
    } else {
        $output = "";
    }

    $defaultCorePath = trim($output) ?: '/absolute/path/to/modx/core/';
    define('MODX_CORE_PATH', $defaultCorePath);
}

class ComponentTest extends TestCase
{
    public function testGettingModxInstance()
    {
         /** @var \MODX\CLI\Application|\PHPUnit\Framework\MockObject\MockObject $app */
        $app = $this->createMock('MODX\CLI\Application');
        $app->expects($this->once())->method('getMODX');

        $config = new Component($app);
    }

    /**
     * @var array $items
     *
     * @dataProvider getData
     */
    public function testItemsInConstructors($items)
    {
        $app = $this->createMock('MODX\CLI\Application');
        $config = new Component($app, $items);

        $this->assertEquals($items, $config->getAll(), 'Items passed in the constructor are available');
    }

    /**
     * @var array $items
     *
     * @dataProvider getData
     */
    public function testGettingItem($items)
    {
        $app = $this->createMock('MODX\CLI\Application');
        $config = new Component($app, $items);

        $this->assertEquals($items['namespace'], $config->get('namespace'), 'Items are retrievable');
    }

    public function testSaveShouldFailIfNoModx()
    {
        $app = $this->createMock('MODX\CLI\Application');
        $config = new Component($app);

        $this->assertFalse($config->save(), 'Saving components configuration is not possible with no modX instance');
    }

    public function testItemsShouldBeEmptyIfNoModx()
    {
        $app = $this->createMock('MODX\CLI\Application');
        $config = new Component($app);

        $this->assertEmpty($config->getAll(), 'Items are empty when no modX instance is available');
    }

    /**
     * @param array $items
     *
     * @dataProvider getData
     */
    public function testItemsLoadedFromModx($items)
    {
        $json = json_encode($items);

        $modx = $this->getMockBuilder('MODX\Revolution\modX')
        ->disableOriginalConstructor()
        ->onlyMethods(['getOption', 'fromJSON'])
        ->getMock();

        $modx->expects($this->once())->method('getOption')->with('console_commands', null, '{}')->will($this->returnValue($json));
        $modx->expects($this->once())->method('fromJSON')->with($json)->will($this->returnValue($items));

        $app = $this->createMock('MODX\CLI\Application');
        $app->method('getMODX')->willReturn($modx);

        $config = new Component($app);

        $this->assertEquals($items, $config->getAll(), 'Items retrieved from modX should be available');
    }

    public function testSave()
    {
        $modx = $this->getMockBuilder('MODX\Revolution\modX')
        ->disableOriginalConstructor()
        ->onlyMethods(['getObject', 'newObject', 'getCacheManager', 'fromJSON', 'getOption', 'toJSON'])
        ->getMock();

        $app = $this->createMock('MODX\CLI\Application');
        $app->method('getMODX')->willReturn($modx);

        $config = new Component($app);
        $config->set('dummy', ['service' => 'Fake']);

        $setting = $this->getMockBuilder(modSystemSetting::class)
        ->disableOriginalConstructor()
            ->onlyMethods(['set', 'save'])
            ->getMock();
        $setting->expects($this->once())->method('save')->willReturn($setting);

        $modx->expects($this->once())->method('getObject')->with('modSystemSetting', ['key' => 'console_commands'])->willReturn($setting);

        $cache = $this->getMockBuilder('MODX\Revolution\modCacheManager')
            ->disableOriginalConstructor()
            ->onlyMethods(['refresh'])
            ->getMock();
        $cache->expects($this->once())->method('refresh')->willReturn(true);

        $modx->expects($this->once())->method('getCacheManager')->willReturn($cache);

        $this->assertTrue($config->save(), 'Saving components services is possible');
    }

    public function testSaveShouldCreateSystemSetting()
    {
        $modx = $this->getMockBuilder('MODX\Revolution\modX')
        ->disableOriginalConstructor()
        ->onlyMethods(['getObject', 'newObject', 'getCacheManager', 'fromJSON', 'getOption', 'toJSON'])
        ->getMock();

        $app = $this->createMock('MODX\CLI\Application');
        $app->method('getMODX')->willReturn($modx);

        $config = new Component($app);
        $config->set('dummy', ['service' => 'Fake']);

        $setting = $this->getMockBuilder(modSystemSetting::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['set', 'save'])
            ->getMock();
        $setting->expects($this->once())->method('save')->willReturn($setting);

        $modx->expects($this->once())->method('getObject')->with('modSystemSetting', ['key' => 'console_commands'])->willReturn(null);
        $modx->expects($this->once())->method('newObject')->with('modSystemSetting')->willReturn($setting);

        $cache = $this->getMockBuilder('MODX\Revolution\modCacheManager')
            ->disableOriginalConstructor()
            ->onlyMethods(['refresh'])
            ->getMock();
        $cache->expects($this->once())->method('refresh')->willReturn(true);

        $modx->expects($this->once())->method('getCacheManager')->willReturn($cache);

        $this->assertTrue($config->save(), 'Saving components services creates the appropriate system setting');
    }

    public function testSaveShouldFail()
    {
        $modx = $this->getMockBuilder('MODX\Revolution\modX')
        ->disableOriginalConstructor()
        ->onlyMethods(['getObject', 'newObject', 'getCacheManager', 'fromJSON', 'getOption', 'toJSON'])
        ->getMock();

        $app = $this->createMock('MODX\CLI\Application');
        $app->method('getMODX')->willReturn($modx);

        $config = new Component($app);
        $config->set('dummy', ['service' => 'Fake']);

        $setting = $this->getMockBuilder(modSystemSetting::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['set', 'save'])
            ->getMock();
        $setting->expects($this->once())->method('save')->willReturn(false);

        $modx->expects($this->once())->method('getObject')->with('modSystemSetting', ['key' => 'console_commands'])->willReturn($setting);

        $this->assertFalse($config->save(), 'Failing to save system setting should not trigger a cache refresh');
    }

    public function getData()
    {
        return array(
            array(
                array(
                    'namespace' => array(
                        'service' => 'FakeService',
                        'params' => array(
                            'key' => 'value'
                        ),
                    ),
                ),
            ),
        );
    }
}
