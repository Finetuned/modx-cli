<?php

namespace MODX\CLI\Tests\Configuration;

use PHPUnit\Framework\TestCase;
use MODX\CLI\Configuration\Component;
use MODX\Revolution\modSystemSetting;

class ComponentTest extends TestCase
{
    private string $originalHome = '';
    private string $tempHome = '';

    protected function setUp(): void
    {
        $this->ensureModxCorePathDefined();
        parent::setUp();
        $this->originalHome = (string) getenv('HOME');
        $this->tempHome = sys_get_temp_dir() . '/modx_cli_home';

        if (!is_dir($this->tempHome)) {
            mkdir($this->tempHome, 0777, true);
        }

        putenv('HOME=' . $this->tempHome);
    }

    protected function tearDown(): void
    {
        $configDir = $this->tempHome . '/.modx';
        $configFile = $configDir . '/components.json';

        if (is_file($configFile)) {
            unlink($configFile);
        }

        if (is_dir($configDir)) {
            rmdir($configDir);
        }

        if ($this->originalHome !== '') {
            putenv('HOME=' . $this->originalHome);
        }

        if (is_dir($this->tempHome)) {
            rmdir($this->tempHome);
        }

        parent::tearDown();
    }

    private function ensureModxCorePathDefined(): void
    {
        if (defined('MODX_CORE_PATH')) {
            return;
        }

        // Use the CLI command to get the default MODX_CORE_PATH
        $output = shell_exec('bin/modx config:get-default');

        if (preg_match('/Base path:\s*(.+)/', (string) $output, $matches)) {
            $basePath = trim($matches[1]);
            $output = $basePath . 'core/';
        } else {
            $output = '';
        }

        $defaultCorePath = trim((string) $output) ?: '/absolute/path/to/modx/core/';
        define('MODX_CORE_PATH', $defaultCorePath);
    }

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

        $modx->expects($this->once())->method('getObject')->with(\MODX\Revolution\modSystemSetting::class, ['key' => 'console_commands'], $this->anything())->willReturn($setting);

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

        $modx->expects($this->once())->method('getObject')->with(\MODX\Revolution\modSystemSetting::class, ['key' => 'console_commands'], $this->anything())->willReturn(null);
        $modx->expects($this->once())->method('newObject')->with(\MODX\Revolution\modSystemSetting::class)->willReturn($setting);

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

        $modx->expects($this->once())->method('getObject')->with(\MODX\Revolution\modSystemSetting::class, ['key' => 'console_commands'], $this->anything())->willReturn($setting);

        $this->assertFalse($config->save(), 'Failing to save system setting should not trigger a cache refresh');
    }

    public static function getData()
    {
        return [
            [
                [
                    'namespace' => [
                        'service' => 'FakeService',
                        'params' => [
                            'key' => 'value'
                        ],
                    ],
                ],
            ],
        ];
    }
}
