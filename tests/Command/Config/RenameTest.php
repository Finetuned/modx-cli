<?php

namespace MODX\CLI\Tests\Command\Config;

use MODX\CLI\Command\Config\Rename;
use MODX\CLI\Tests\Configuration\BaseTest;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Tester\CommandTester;

class RenameTest extends BaseTest
{
    protected $command;
    protected $commandTester;
    protected $app;
    protected $instances;

    protected function setUp(): void
    {
        $this->instances = new FakeConfigStore([
            'old' => ['base_path' => '/path/old/'],
            'new' => ['base_path' => '/path/new/'],
            '__default__' => ['class' => 'old'],
        ]);

        $this->app = $this->getMockBuilder(\MODX\CLI\Application::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->app->instances = $this->instances;
        $helperSet = new HelperSet([new QuestionHelper()]);
        $this->app->method('getHelperSet')->willReturn($helperSet);
        $this->app->method('getDefinition')->willReturn(new InputDefinition());

        $this->command = new Rename();
        $this->command->setApplication($this->app);
        $this->command->setHelperSet($helperSet);

        $this->commandTester = new CommandTester($this->command);
    }

    public function testConfigureHasCorrectName()
    {
        $this->assertEquals('config:rename', $this->command->getName());
    }

    public function testConfigureHasCorrectDescription()
    {
        $this->assertEquals('Rename a MODX instance in the configuration', $this->command->getDescription());
    }

    public function testConfigureHasArguments()
    {
        $definition = $this->command->getDefinition();
        $this->assertTrue($definition->hasArgument('old_name'));
        $this->assertTrue($definition->getArgument('old_name')->isRequired());
        $this->assertTrue($definition->hasArgument('new_name'));
        $this->assertTrue($definition->getArgument('new_name')->isRequired());
    }

    public function testExecuteWithMissingInstance()
    {
        $this->commandTester->execute([
            'old_name' => 'missing',
            'new_name' => 'renamed'
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString("Instance 'missing' does not exist", $output);
        $this->assertEquals(1, $this->commandTester->getStatusCode());
    }

    public function testExecuteRenamesInstanceAndDefault()
    {
        $this->commandTester->execute([
            'old_name' => 'old',
            'new_name' => 'renamed'
        ]);

        $this->assertNull($this->instances->get('old'));
        $this->assertEquals('/path/old/', $this->instances->get('renamed')['base_path']);
        $this->assertEquals('renamed', $this->instances->get('__default__')['class']);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString("Instance 'old' renamed to 'renamed' and set as default", $output);
    }

    public function testExecuteWithExistingNewNameAborts()
    {
        $this->commandTester->setInputs(['no']);
        $this->commandTester->execute([
            'old_name' => 'old',
            'new_name' => 'new'
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Operation aborted', $output);
        $this->assertNotNull($this->instances->get('old'));
        $this->assertNotNull($this->instances->get('new'));
    }
}
