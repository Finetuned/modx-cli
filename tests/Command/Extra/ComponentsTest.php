<?php

namespace MODX\CLI\Tests\Command\Extra;

use MODX\CLI\Command\Extra\Components;
use MODX\CLI\Tests\Configuration\BaseTest;
use Symfony\Component\Console\Tester\CommandTester;

class ComponentsTest extends BaseTest
{
    protected $command;
    protected $commandTester;
    protected $modx;

    protected function setUp(): void
    {
        $this->modx = $this->createMock('MODX\Revolution\modX');
        $this->command = new Components();
        $this->command->modx = $this->modx;
        $this->commandTester = new CommandTester($this->command);
    }

    public function testExecuteWithNoNamespacesJsonOutput()
    {
        $this->modx->method('getCollection')
            ->with(\MODX\Revolution\modNamespace::class)
            ->willReturn([]);

        $this->commandTester->execute([
            '--json' => true
        ]);

        $decoded = json_decode($this->commandTester->getDisplay(), true);
        $this->assertEquals(0, $decoded['total']);
        $this->assertEquals([], $decoded['results']);
    }

    public function testExecuteWithComponentsJsonOutput()
    {
        $basePath = sys_get_temp_dir() . '/modx-cli-extra-components/';
        $controllerPath = $basePath . 'components/demo/controllers/index.php';
        @mkdir(dirname($controllerPath), 0777, true);
        @file_put_contents($controllerPath, 'test');

        $coreNamespace = $this->getMockBuilder('stdClass')
            ->addMethods(['get'])
            ->getMock();
        $coreNamespace->method('get')->willReturnMap([
            ['name', 'core'],
            ['path', 'core/'],
        ]);

        $demoNamespace = $this->getMockBuilder('stdClass')
            ->addMethods(['get'])
            ->getMock();
        $demoNamespace->method('get')->willReturnMap([
            ['name', 'demo'],
            ['path', 'components/demo/'],
        ]);

        $this->modx->method('getCollection')
            ->with(\MODX\Revolution\modNamespace::class)
            ->willReturn([$coreNamespace, $demoNamespace]);
        $this->modx->method('getOption')
            ->with('base_path')
            ->willReturn($basePath);

        $this->commandTester->execute([
            '--json' => true
        ]);

        $decoded = json_decode($this->commandTester->getDisplay(), true);
        $this->assertEquals(1, $decoded['total']);
        $this->assertEquals('demo', $decoded['results'][0]['name']);
        $this->assertEquals('components/demo/', $decoded['results'][0]['path']);
        $this->assertEquals('components/demo/controllers/index.php', $decoded['results'][0]['controller']);

        @unlink($controllerPath);
        @rmdir(dirname($controllerPath));
        @rmdir(dirname(dirname($controllerPath)));
        @rmdir(dirname(dirname(dirname($controllerPath))));
        @rmdir($basePath);
    }
}
