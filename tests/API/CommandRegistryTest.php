<?php

namespace MODX\CLI\Tests\API;

use MODX\CLI\API\CommandRegistry;
use MODX\CLI\API\ClosureCommand;
use MODX\CLI\Command\BaseCmd;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;

class CommandRegistryTest extends TestCase
{
    /**
     * @var CommandRegistry
     */
    private $registry;

    protected function setUp(): void
    {
        $this->registry = new CommandRegistry();
    }

    public function testRegisterClosureCommand()
    {
        $name = 'test:command';
        $callable = function ($args, $assoc_args) {
            return 0;
        };
        
        $result = $this->registry->register($name, $callable, [
            'shortdesc' => 'Test command',
            'longdesc' => 'This is a test command'
        ]);
        
        $this->assertTrue($result);
        $this->assertTrue($this->registry->has($name));
        
        $command = $this->registry->get($name);
        $this->assertInstanceOf(ClosureCommand::class, $command);
        $this->assertEquals($name, $command->getName());
        $this->assertEquals('Test command', $command->getDescription());
        $this->assertEquals('This is a test command', $command->getHelp());
    }

    public function testRegisterCommandClass()
    {
        $name = 'test:class-command';
        $callable = new TestCommand();
        
        $result = $this->registry->register($name, $callable);
        
        $this->assertTrue($result);
        $this->assertTrue($this->registry->has($name));
        
        $command = $this->registry->get($name);
        $this->assertInstanceOf(TestCommand::class, $command);
        $this->assertEquals($name, $command->getName());
    }

    public function testRegisterCommandClassName()
    {
        $name = 'test:class-name-command';
        $callable = TestCommand::class;
        
        $result = $this->registry->register($name, $callable);
        
        $this->assertTrue($result);
        $this->assertTrue($this->registry->has($name));
        
        $command = $this->registry->get($name);
        $this->assertInstanceOf(TestCommand::class, $command);
        $this->assertEquals($name, $command->getName());
    }

    public function testRegisterDeferredCommand()
    {
        $name = 'test:deferred-command';
        $callable = function ($args, $assoc_args) {
            return 0;
        };
        
        $result = $this->registry->register($name, $callable, [
            'is_deferred' => true
        ]);
        
        $this->assertFalse($result); // Should return false for deferred commands
        $this->assertTrue($this->registry->has($name));
        
        $command = $this->registry->get($name);
        $this->assertInstanceOf(ClosureCommand::class, $command);
        $this->assertEquals($name, $command->getName());
    }

    public function testUnregisterCommand()
    {
        $name = 'test:unregister-command';
        $callable = function ($args, $assoc_args) {
            return 0;
        };
        
        $this->registry->register($name, $callable);
        $this->assertTrue($this->registry->has($name));
        
        $result = $this->registry->unregister($name);
        
        $this->assertTrue($result);
        $this->assertFalse($this->registry->has($name));
        $this->assertNull($this->registry->get($name));
    }

    public function testUnregisterNonExistentCommand()
    {
        $result = $this->registry->unregister('non:existent');
        
        $this->assertFalse($result);
    }

    public function testGetAllCommands()
    {
        $this->registry->register('test:command1', function ($args, $assoc_args) {
            return 0;
        });
        
        $this->registry->register('test:command2', function ($args, $assoc_args) {
            return 0;
        });
        
        $commands = $this->registry->getAll();
        
        $this->assertCount(2, $commands);
        $this->assertContainsOnlyInstancesOf(Command::class, $commands);
    }

    public function testInvalidCommandImplementation()
    {
        $this->expectException(\Exception::class);
        $this->registry->register('test:invalid', 'not-a-callable');
    }
}

/**
 * Test command class for testing the registry
 */
class TestCommand extends Command
{
    protected static $defaultName = 'test:command';
    
    protected function configure()
    {
        $this->setDescription('Test command');
        $this->setHelp('This is a test command');
    }
    
    protected function execute(\Symfony\Component\Console\Input\InputInterface $input, \Symfony\Component\Console\Output\OutputInterface $output)
    {
        return 0;
    }
}
