<?php namespace MODX\CLI\Tests\Command\Config;

use MODX\CLI\Command\Config\Add;
use MODX\CLI\Tests\Configuration\BaseTest;
use MODX\CLI\Application;
use Symfony\Component\Console\Tester\CommandTester;

class AddTest extends BaseTest
{
    protected $command;
    protected $commandTester;
    protected $application;

    protected function setUp(): void
    {
        // Create the application
        
        // Create the command
        $this->command = new Add();
        
        // Create a command tester
        $this->commandTester = new CommandTester($this->command);
    }

    public function testConfigureHasCorrectName()
    {
        $this->assertEquals('config:add', $this->command->getName());
    }

    public function testConfigureHasCorrectDescription()
    {
        $this->assertEquals('Add a MODX instance to the configuration', $this->command->getDescription());
    }

    public function testConfigureHasCorrectHelp()
    {
        $this->assertStringContainsString('adds a MODX instance to the configuration', $this->command->getHelp());
    }

    public function testConfigureHasNameArgument()
    {
        $definition = $this->command->getDefinition();
        $this->assertTrue($definition->hasArgument('name'));
        $this->assertTrue($definition->getArgument('name')->isRequired());
    }

    public function testConfigureHasBasePathOption()
    {
        $definition = $this->command->getDefinition();
        $this->assertTrue($definition->hasOption('base_path'));
        $this->assertTrue($definition->getOption('base_path')->isValueRequired());
    }

    public function testConfigureHasDefaultOption()
    {
        $definition = $this->command->getDefinition();
        $this->assertTrue($definition->hasOption('default'));
        $this->assertFalse($definition->getOption('default')->acceptValue());
    }
}
