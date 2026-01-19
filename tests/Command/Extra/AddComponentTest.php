<?php

namespace MODX\CLI\Tests\Command\Extra;

use MODX\CLI\Command\Extra\AddComponent;
use MODX\CLI\Tests\Configuration\BaseTest;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Tester\CommandTester;

class AddComponentTest extends BaseTest
{
    protected $command;
    protected $commandTester;
    protected $modx;

    protected function setUp(): void
    {
        $this->modx = $this->createMock('MODX\Revolution\modX');
        $this->command = new AddComponent();
        $this->command->modx = $this->modx;
        $this->command->setHelperSet(new HelperSet([new QuestionHelper()]));
        $this->commandTester = new CommandTester($this->command);
    }

    public function testConfigureHasCorrectName()
    {
        $this->assertEquals('extra:add-component', $this->command->getName());
    }

    public function testConfigureHasCorrectDescription()
    {
        $this->assertEquals('Add a component to MODX', $this->command->getDescription());
    }

    public function testConfigureHasArgumentsAndOptions()
    {
        $definition = $this->command->getDefinition();
        $this->assertTrue($definition->hasArgument('namespace'));
        $this->assertTrue($definition->getArgument('namespace')->isRequired());
        $this->assertTrue($definition->hasOption('path'));
        $this->assertTrue($definition->getOption('path')->isValueRequired());
        $this->assertTrue($definition->hasOption('assets_path'));
        $this->assertTrue($definition->getOption('assets_path')->isValueRequired());
        $this->assertTrue($definition->hasOption('force'));
        $this->assertFalse($definition->getOption('force')->acceptValue());
    }

    public function testExecuteCreatesComponentWithDefaults()
    {
        $basePath = sys_get_temp_dir() . '/modx-cli-extra-add/';
        @mkdir($basePath, 0777, true);

        $namespaceData = [];
        $namespace = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['set', 'get', 'save'])
            ->getMock();
        $namespace->method('set')
            ->willReturnCallback(function ($key, $value) use (&$namespaceData) {
                $namespaceData[$key] = $value;
            });
        $namespace->method('get')
            ->willReturnCallback(function ($key) use (&$namespaceData) {
                return $namespaceData[$key] ?? null;
            });
        $namespace->method('save')->willReturn(true);

        $menu = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['fromArray', 'save'])
            ->getMock();
        $menu->method('save')->willReturn(true);

        $this->modx->method('getObject')
            ->willReturn(null);
        $this->modx->method('newObject')
            ->willReturnCallback(function ($class) use ($namespace, $menu) {
                if ($class === \MODX\Revolution\modNamespace::class) {
                    return $namespace;
                }
                if ($class === \MODX\Revolution\modMenu::class) {
                    return $menu;
                }
                return null;
            });
        $this->modx->method('getOption')
            ->with('base_path')
            ->willReturn($basePath);

        $this->commandTester->execute([
            'namespace' => 'demo',
            '--force' => true
        ]);

        $this->assertEquals('components/demo/', $namespace->get('path'));
        $this->assertEquals('assets/components/demo/', $namespace->get('assets_path'));

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString("Component 'demo' created successfully", $output);

        $this->removeDirectory($basePath);
    }

    public function testExecuteCreatesComponentWithJsonOutput()
    {
        $basePath = sys_get_temp_dir() . '/modx-cli-extra-add-json/';
        @mkdir($basePath, 0777, true);

        $namespaceData = [];
        $namespace = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['set', 'get', 'save'])
            ->getMock();
        $namespace->method('set')
            ->willReturnCallback(function ($key, $value) use (&$namespaceData) {
                $namespaceData[$key] = $value;
            });
        $namespace->method('get')
            ->willReturnCallback(function ($key) use (&$namespaceData) {
                return $namespaceData[$key] ?? null;
            });
        $namespace->method('save')->willReturn(true);

        $menu = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['fromArray', 'save'])
            ->getMock();
        $menu->method('save')->willReturn(true);

        $this->modx->method('getObject')
            ->willReturn(null);
        $this->modx->method('newObject')
            ->willReturnCallback(function ($class) use ($namespace, $menu) {
                if ($class === \MODX\Revolution\modNamespace::class) {
                    return $namespace;
                }
                if ($class === \MODX\Revolution\modMenu::class) {
                    return $menu;
                }
                return null;
            });
        $this->modx->method('getOption')
            ->with('base_path')
            ->willReturn($basePath);

        $this->commandTester->execute([
            'namespace' => 'demo',
            '--force' => true,
            '--json' => true
        ]);

        $decoded = json_decode($this->commandTester->getDisplay(), true);
        $this->assertTrue($decoded['success']);
        $this->assertEquals("Component 'demo' created successfully", $decoded['message']);
        $this->assertEquals('demo', $decoded['namespace']);
        $this->assertEquals('components/demo/', $decoded['path']);
        $this->assertEquals('assets/components/demo/', $decoded['assets_path']);
        $this->assertFalse($decoded['updated']);

        $this->removeDirectory($basePath);
    }

    public function testExecuteWithExistingNamespaceAborts()
    {
        $existing = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['save'])
            ->getMock();
        $existing->method('save')->willReturn(true);

        $this->modx->method('getObject')
            ->with(\MODX\Revolution\modNamespace::class, 'demo')
            ->willReturn($existing);

        $this->commandTester->setInputs(['no']);
        $this->commandTester->execute([
            'namespace' => 'demo'
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Operation aborted', $output);
    }

    private function removeDirectory($dir)
    {
        if (!file_exists($dir)) {
            return;
        }
        if (!is_dir($dir)) {
            @unlink($dir);
            return;
        }
        foreach (scandir($dir) as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $this->removeDirectory($dir . DIRECTORY_SEPARATOR . $item);
        }
        @rmdir($dir);
    }
}
