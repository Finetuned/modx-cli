<?php

namespace MODX\CLI\Tests\Command\Extra;

use MODX\CLI\Command\Extra\RemoveComponent;
use MODX\CLI\Tests\Configuration\BaseTest;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Tester\CommandTester;

class RemoveComponentTest extends BaseTest
{
    protected $command;
    protected $commandTester;
    protected $modx;

    protected function setUp(): void
    {
        $this->modx = $this->createMock('MODX\Revolution\modX');
        $this->command = new RemoveComponent();
        $this->command->modx = $this->modx;
        $this->command->setHelperSet(new HelperSet([new QuestionHelper()]));
        $this->commandTester = new CommandTester($this->command);
    }

    public function testConfigureHasCorrectName()
    {
        $this->assertEquals('extra:remove-component', $this->command->getName());
    }

    public function testConfigureHasCorrectDescription()
    {
        $this->assertEquals('Remove a component from MODX', $this->command->getDescription());
    }

    public function testConfigureHasArgumentsAndOptions()
    {
        $definition = $this->command->getDefinition();
        $this->assertTrue($definition->hasArgument('namespace'));
        $this->assertTrue($definition->getArgument('namespace')->isRequired());
        $this->assertTrue($definition->hasOption('force'));
        $this->assertFalse($definition->getOption('force')->acceptValue());
        $this->assertTrue($definition->hasOption('files'));
        $this->assertFalse($definition->getOption('files')->acceptValue());
    }

    public function testExecuteWithMissingNamespace()
    {
        $this->modx->method('getObject')
            ->with(\MODX\Revolution\modNamespace::class, 'demo')
            ->willReturn(null);

        $this->commandTester->execute([
            'namespace' => 'demo'
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString("Namespace 'demo' does not exist", $output);
        $this->assertEquals(1, $this->commandTester->getStatusCode());
    }

    public function testExecuteRemovesComponentAndFiles()
    {
        $basePath = sys_get_temp_dir() . '/modx-cli-extra-remove/';
        $corePath = $basePath . 'components/demo/';
        $assetsPath = $basePath . 'assets/components/demo/';
        @mkdir($corePath, 0777, true);
        @mkdir($assetsPath, 0777, true);
        @file_put_contents($corePath . 'test.txt', 'data');
        @file_put_contents($assetsPath . 'test.txt', 'data');

        $namespace = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['get', 'remove'])
            ->getMock();
        $namespace->method('get')
            ->willReturnMap([
                ['path', 'components/demo/'],
                ['assets_path', 'assets/components/demo/'],
            ]);
        $namespace->method('remove')->willReturn(true);

        $menu = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['remove'])
            ->getMock();
        $menu->method('remove')->willReturn(true);

        $this->modx->method('getObject')
            ->willReturnCallback(function($class, $criteria) use ($namespace, $menu) {
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
            '--files' => true
        ]);

        $this->assertFalse(file_exists($corePath));
        $this->assertFalse(file_exists($assetsPath));

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString("Component 'demo' removed successfully", $output);
    }
}
