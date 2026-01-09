<?php

namespace MODX\CLI\Tests\Command\Config;

use MODX\CLI\Command\Config\Rm;
use MODX\CLI\Tests\Configuration\BaseTest;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Tester\CommandTester;

class RmTest extends BaseTest
{
    protected $command;
    protected $commandTester;
    protected $app;
    protected $instances;

    protected function setUp(): void
    {
        $this->instances = new FakeConfigStore([
            'site' => ['base_path' => '/path/site/'],
            '__default__' => ['class' => 'site'],
        ]);

        $this->app = $this->getMockBuilder(\MODX\CLI\Application::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->app->instances = $this->instances;
        $helperSet = new HelperSet([new QuestionHelper()]);
        $this->app->method('getHelperSet')->willReturn($helperSet);
        $this->app->method('getDefinition')->willReturn(new InputDefinition());

        $this->command = new Rm();
        $this->command->setApplication($this->app);
        $this->command->setHelperSet($helperSet);

        $this->commandTester = new CommandTester($this->command);
    }

    public function testConfigureHasCorrectName()
    {
        $this->assertEquals('config:rm', $this->command->getName());
    }

    public function testConfigureHasCorrectDescription()
    {
        $this->assertEquals('Remove a MODX instance from the configuration', $this->command->getDescription());
    }

    public function testConfigureHasArgument()
    {
        $definition = $this->command->getDefinition();
        $this->assertTrue($definition->hasArgument('name'));
        $this->assertTrue($definition->getArgument('name')->isRequired());
    }

    public function testExecuteWithMissingInstance()
    {
        $this->commandTester->execute([
            'name' => 'missing'
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString("Instance 'missing' does not exist", $output);
        $this->assertEquals(1, $this->commandTester->getStatusCode());
    }

    public function testExecuteRemovesDefaultInstanceWithConfirmation()
    {
        $this->commandTester->setInputs(['yes']);
        $this->commandTester->execute([
            'name' => 'site'
        ]);

        $this->assertNull($this->instances->get('site'));
        $this->assertNull($this->instances->get('__default__'));

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString("Instance 'site' removed", $output);
    }

    public function testExecuteRemovesInstanceWithJsonOutput()
    {
        $this->instances->set('__default__', ['class' => 'other']);
        $this->commandTester->execute([
            'name' => 'site',
            '--json' => true
        ]);

        $decoded = json_decode($this->commandTester->getDisplay(), true);
        $this->assertTrue($decoded['success']);
        $this->assertEquals("Instance 'site' removed", $decoded['message']);
        $this->assertEquals('site', $decoded['instance']['name']);
        $this->assertFalse($decoded['instance']['was_default']);
    }
}
