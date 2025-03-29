<?php

namespace MODX\CLI\Tests\API;

use MODX\CLI\API\CommandRegistry;
use MODX\CLI\API\CommandRunner;
use MODX\CLI\API\HookRegistry;
use MODX\CLI\API\MODX_CLI;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;

class MODX_CLITest extends TestCase
{
    protected function setUp(): void
    {
        // Reset the MODX_CLI static instance for each test
        $reflection = new \ReflectionClass(MODX_CLI::class);
        $property = $reflection->getProperty('instance');
        $property->setAccessible(true);
        $property->setValue(null, null);
    }
    
    public function testAddCommand()
    {
        // Create a mock CommandRegistry
        $registry = $this->createMock(CommandRegistry::class);
        $registry->expects($this->once())
            ->method('register')
            ->with(
                $this->equalTo('test:command'),
                $this->callback(function ($callable) {
                    return is_callable($callable);
                }),
                $this->equalTo(['shortdesc' => 'Test command'])
            )
            ->willReturn(true);
        
        // Set the mock registry in MODX_CLI
        $reflection = new \ReflectionClass(MODX_CLI::class);
        $property = $reflection->getProperty('instance');
        $property->setAccessible(true);
        $instance = $reflection->newInstanceWithoutConstructor();
        $property->setValue(null, $instance);
        
        $registryProperty = $reflection->getProperty('commandRegistry');
        $registryProperty->setAccessible(true);
        $registryProperty->setValue($instance, $registry);
        
        // Call the add_command method
        $result = MODX_CLI::add_command('test:command', function () {
            return 0;
        }, ['shortdesc' => 'Test command']);
        
        $this->assertTrue($result);
    }
    
    public function testRemoveCommand()
    {
        // Create a mock CommandRegistry
        $registry = $this->createMock(CommandRegistry::class);
        $registry->expects($this->once())
            ->method('unregister')
            ->with($this->equalTo('test:command'))
            ->willReturn(true);
        
        // Set the mock registry in MODX_CLI
        $reflection = new \ReflectionClass(MODX_CLI::class);
        $property = $reflection->getProperty('instance');
        $property->setAccessible(true);
        $instance = $reflection->newInstanceWithoutConstructor();
        $property->setValue(null, $instance);
        
        $registryProperty = $reflection->getProperty('commandRegistry');
        $registryProperty->setAccessible(true);
        $registryProperty->setValue($instance, $registry);
        
        // Call the remove_command method
        $result = MODX_CLI::remove_command('test:command');
        
        $this->assertTrue($result);
    }
    
    public function testGetCommand()
    {
        // Create a mock command
        $command = $this->createMock(Command::class);
        
        // Create a mock CommandRegistry
        $registry = $this->createMock(CommandRegistry::class);
        $registry->expects($this->once())
            ->method('get')
            ->with($this->equalTo('test:command'))
            ->willReturn($command);
        
        // Set the mock registry in MODX_CLI
        $reflection = new \ReflectionClass(MODX_CLI::class);
        $property = $reflection->getProperty('instance');
        $property->setAccessible(true);
        $instance = $reflection->newInstanceWithoutConstructor();
        $property->setValue(null, $instance);
        
        $registryProperty = $reflection->getProperty('commandRegistry');
        $registryProperty->setAccessible(true);
        $registryProperty->setValue($instance, $registry);
        
        // Call the get_command method
        $result = MODX_CLI::get_command('test:command');
        
        $this->assertSame($command, $result);
    }
    
    public function testGetCommands()
    {
        // Create mock commands
        $command1 = $this->createMock(Command::class);
        $command2 = $this->createMock(Command::class);
        
        // Create a mock CommandRegistry
        $registry = $this->createMock(CommandRegistry::class);
        $registry->expects($this->once())
            ->method('getAll')
            ->willReturn([$command1, $command2]);
        
        // Set the mock registry in MODX_CLI
        $reflection = new \ReflectionClass(MODX_CLI::class);
        $property = $reflection->getProperty('instance');
        $property->setAccessible(true);
        $instance = $reflection->newInstanceWithoutConstructor();
        $property->setValue(null, $instance);
        
        $registryProperty = $reflection->getProperty('commandRegistry');
        $registryProperty->setAccessible(true);
        $registryProperty->setValue($instance, $registry);
        
        // Call the get_commands method
        $result = MODX_CLI::get_commands();
        
        $this->assertCount(2, $result);
        $this->assertSame($command1, $result[0]);
        $this->assertSame($command2, $result[1]);
    }
    
    public function testRunCommand()
    {
        // Create a mock CommandRunner
        $runner = $this->createMock(CommandRunner::class);
        $runner->expects($this->once())
            ->method('run')
            ->with(
                $this->equalTo('test:command'),
                $this->equalTo(['arg1' => 'value1']),
                $this->equalTo(['return' => true])
            )
            ->willReturn((object) [
                'return_code' => 0,
                'stdout' => 'Command output',
                'stderr' => ''
            ]);
        
        // Set the mock runner in MODX_CLI
        $reflection = new \ReflectionClass(MODX_CLI::class);
        $property = $reflection->getProperty('instance');
        $property->setAccessible(true);
        $instance = $reflection->newInstanceWithoutConstructor();
        $property->setValue(null, $instance);
        
        $runnerProperty = $reflection->getProperty('commandRunner');
        $runnerProperty->setAccessible(true);
        $runnerProperty->setValue($instance, $runner);
        
        // Call the run_command method
        $result = MODX_CLI::run_command('test:command', ['arg1' => 'value1'], ['return' => true]);
        
        $this->assertEquals(0, $result->return_code);
        $this->assertEquals('Command output', $result->stdout);
        $this->assertEquals('', $result->stderr);
    }
    
    public function testRegisterHook()
    {
        // Create a mock HookRegistry
        $registry = $this->createMock(HookRegistry::class);
        $registry->expects($this->once())
            ->method('register')
            ->with(
                $this->equalTo('test:hook'),
                $this->callback(function ($callable) {
                    return is_callable($callable);
                })
            )
            ->willReturn(true);
        
        // Set the mock registry in MODX_CLI
        $reflection = new \ReflectionClass(MODX_CLI::class);
        $property = $reflection->getProperty('instance');
        $property->setAccessible(true);
        $instance = $reflection->newInstanceWithoutConstructor();
        $property->setValue(null, $instance);
        
        $registryProperty = $reflection->getProperty('hookRegistry');
        $registryProperty->setAccessible(true);
        $registryProperty->setValue($instance, $registry);
        
        // Call the register_hook method
        $result = MODX_CLI::register_hook('test:hook', function () {
            return 'test';
        });
        
        $this->assertTrue($result);
    }
    
    public function testAddHook()
    {
        // Create a mock HookRegistry
        $registry = $this->createMock(HookRegistry::class);
        $registry->expects($this->once())
            ->method('register')
            ->with(
                $this->equalTo('test:hook'),
                $this->callback(function ($callable) {
                    return is_callable($callable);
                })
            )
            ->willReturn(true);
        
        // Set the mock registry in MODX_CLI
        $reflection = new \ReflectionClass(MODX_CLI::class);
        $property = $reflection->getProperty('instance');
        $property->setAccessible(true);
        $instance = $reflection->newInstanceWithoutConstructor();
        $property->setValue(null, $instance);
        
        $registryProperty = $reflection->getProperty('hookRegistry');
        $registryProperty->setAccessible(true);
        $registryProperty->setValue($instance, $registry);
        
        // Call the add_hook method
        $result = MODX_CLI::add_hook('test:hook', function () {
            return 'test';
        });
        
        $this->assertTrue($result);
    }
    
    public function testDoHook()
    {
        // Create a mock HookRegistry
        $registry = $this->createMock(HookRegistry::class);
        $registry->expects($this->once())
            ->method('run')
            ->with(
                $this->equalTo('test:hook'),
                $this->equalTo(['arg1', 'arg2'])
            )
            ->willReturn(['result1', 'result2']);
        
        // Set the mock registry in MODX_CLI
        $reflection = new \ReflectionClass(MODX_CLI::class);
        $property = $reflection->getProperty('instance');
        $property->setAccessible(true);
        $instance = $reflection->newInstanceWithoutConstructor();
        $property->setValue(null, $instance);
        
        $registryProperty = $reflection->getProperty('hookRegistry');
        $registryProperty->setAccessible(true);
        $registryProperty->setValue($instance, $registry);
        
        // Call the do_hook method
        $result = MODX_CLI::do_hook('test:hook', ['arg1', 'arg2']);
        
        $this->assertEquals(['result1', 'result2'], $result);
    }
    
    public function testBeforeInvoke()
    {
        // Call the before_invoke method
        $result = MODX_CLI::before_invoke('test:command', function () {
            return 'test';
        });
        
        $this->assertTrue($result);
        
        // Verify the hook was registered
        $reflection = new \ReflectionClass(MODX_CLI::class);
        $property = $reflection->getProperty('instance');
        $property->setAccessible(true);
        $instance = $property->getValue(null);
        
        $registryProperty = $reflection->getProperty('hookRegistry');
        $registryProperty->setAccessible(true);
        $registry = $registryProperty->getValue($instance);
        
        $method = new \ReflectionMethod(HookRegistry::class, 'get');
        $method->setAccessible(true);
        $hooks = $method->invoke($registry, 'before_invoke:test:command');
        
        $this->assertCount(1, $hooks);
    }
    
    public function testAfterInvoke()
    {
        // Call the after_invoke method
        $result = MODX_CLI::after_invoke('test:command', function () {
            return 'test';
        });
        
        $this->assertTrue($result);
        
        // Verify the hook was registered
        $reflection = new \ReflectionClass(MODX_CLI::class);
        $property = $reflection->getProperty('instance');
        $property->setAccessible(true);
        $instance = $property->getValue(null);
        
        $registryProperty = $reflection->getProperty('hookRegistry');
        $registryProperty->setAccessible(true);
        $registry = $registryProperty->getValue($instance);
        
        $method = new \ReflectionMethod(HookRegistry::class, 'get');
        $method->setAccessible(true);
        $hooks = $method->invoke($registry, 'after_invoke:test:command');
        
        $this->assertCount(1, $hooks);
    }
    
    public function testLog()
    {
        // Capture output
        ob_start();
        MODX_CLI::log('Test message');
        $output = ob_get_clean();
        
        $this->assertEquals("Test message\n", $output);
    }
    
    public function testSuccess()
    {
        // Capture output
        ob_start();
        MODX_CLI::success('Test message');
        $output = ob_get_clean();
        
        $this->assertEquals("Success: Test message\n", $output);
    }
    
    public function testWarning()
    {
        // Capture output
        ob_start();
        MODX_CLI::warning('Test message');
        $output = ob_get_clean();
        
        $this->assertEquals("Warning: Test message\n", $output);
    }
    
    public function testError()
    {
        // Capture output
        ob_start();
        MODX_CLI::error('Test message');
        $output = ob_get_clean();
        
        $this->assertEquals("Error: Test message\n", $output);
    }
}
