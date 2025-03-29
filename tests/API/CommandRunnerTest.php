<?php

namespace MODX\CLI\Tests\API;

use MODX\CLI\API\CommandRunner;
use MODX\CLI\API\HookRegistry;
use MODX\CLI\Application;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CommandRunnerTest extends TestCase
{
    /**
     * @var CommandRunner
     */
    private $runner;
    
    /**
     * @var Application&\PHPUnit\Framework\MockObject\MockObject
     */
    private $application;
    
    /**
     * @var HookRegistry&\PHPUnit\Framework\MockObject\MockObject
     */
    private $hookRegistry;
    
    protected function setUp(): void
    {
        $this->application = $this->createMock(Application::class);
        $this->hookRegistry = $this->createMock(HookRegistry::class);
        $this->runner = new CommandRunner($this->application, $this->hookRegistry);
    }
    
    public function testRunCommand()
    {
        $command = $this->createMock(Command::class);
        $command->expects($this->once())
            ->method('run')
            ->willReturn(0);
        
        $this->application->expects($this->once())
            ->method('find')
            ->with('test:command')
            ->willReturn($command);
        
        $result = $this->runner->run('test:command');
        
        $this->assertEquals(0, $result);
    }
    
    public function testRunCommandWithReturnOption()
    {
        $command = $this->createMock(Command::class);
        $command->expects($this->once())
            ->method('run')
            ->willReturnCallback(function ($input, $output) {
                $output->write('Command output');
                return 0;
            });
        
        $this->application->expects($this->once())
            ->method('find')
            ->with('test:command')
            ->willReturn($command);
        
        $result = $this->runner->run('test:command', [], ['return' => true]);
        
        $this->assertIsObject($result);
        $this->assertEquals(0, $result->return_code);
        $this->assertEquals('Command output', $result->stdout);
        $this->assertEquals('', $result->stderr);
    }
    
    public function testRunCommandWithError()
    {
        $command = $this->createMock(Command::class);
        $command->expects($this->once())
            ->method('run')
            ->willThrowException(new \Exception('Command error'));
        
        $this->application->expects($this->once())
            ->method('find')
            ->with('test:command')
            ->willReturn($command);
        
        $result = $this->runner->run('test:command', [], ['return' => true, 'exit_error' => false]);
        
        $this->assertIsObject($result);
        $this->assertEquals(1, $result->return_code);
        $this->assertEquals('', $result->stdout);
        $this->assertEquals('Command error', $result->stderr);
    }
    
    public function testRunCommandWithExitError()
    {
        $command = $this->createMock(Command::class);
        $command->expects($this->once())
            ->method('run')
            ->willThrowException(new \Exception('Command error'));
        
        $this->application->expects($this->once())
            ->method('find')
            ->with('test:command')
            ->willReturn($command);
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Command error');
        
        $this->runner->run('test:command', [], ['exit_error' => true]);
    }
    
    public function testRunCommandWithArguments()
    {
        $command = $this->createMock(Command::class);
        $command->expects($this->once())
            ->method('run')
            ->willReturnCallback(function ($input, $output) {
                $this->assertEquals('value1', $input->getArgument('arg1'));
                $this->assertEquals('value2', $input->getArgument('arg2'));
                return 0;
            });
        
        $this->application->expects($this->once())
            ->method('find')
            ->with('test:command')
            ->willReturn($command);
        
        $result = $this->runner->run('test:command', [
            'arg1' => 'value1',
            'arg2' => 'value2'
        ]);
        
        $this->assertEquals(0, $result);
    }
    
    public function testRunCommandWithOptions()
    {
        $command = $this->createMock(Command::class);
        $command->expects($this->once())
            ->method('run')
            ->willReturnCallback(function ($input, $output) {
                $this->assertEquals('value', $input->getOption('option'));
                return 0;
            });
        
        $this->application->expects($this->once())
            ->method('find')
            ->with('test:command')
            ->willReturn($command);
        
        $result = $this->runner->run('test:command', [
            '--option' => 'value'
        ]);
        
        $this->assertEquals(0, $result);
    }
    
    public function testRunCommandWithParseOption()
    {
        $command = $this->createMock(Command::class);
        $command->expects($this->once())
            ->method('run')
            ->willReturnCallback(function ($input, $output) {
                $this->assertEquals('value', $input->getOption('option'));
                $this->assertEquals('arg_value', $input->getArgument(0));
                return 0;
            });
        
        $this->application->expects($this->once())
            ->method('find')
            ->with('test')
            ->willReturn($command);
        
        $result = $this->runner->run('test --option=value arg_value', [], ['parse' => true]);
        
        $this->assertEquals(0, $result);
    }
    
    public function testRunCommandWithHooks()
    {
        $command = $this->createMock(Command::class);
        $command->expects($this->once())
            ->method('run')
            ->willReturn(0);
        
        $this->application->expects($this->once())
            ->method('find')
            ->with('test:command')
            ->willReturn($command);
        
        $this->hookRegistry->expects($this->exactly(2))
            ->method('run')
            ->withConsecutive(
                ['before_invoke', ['test:command', []]],
                ['after_invoke', ['test:command', [], $this->anything()]]
            );
        
        $result = $this->runner->run('test:command');
        
        $this->assertEquals(0, $result);
    }
    
    public function testRunNonExistentCommand()
    {
        $this->application->expects($this->once())
            ->method('find')
            ->with('non:existent')
            ->willThrowException(new \Exception('Command not found'));
        
        $result = $this->runner->run('non:existent', [], ['return' => true, 'exit_error' => false]);
        
        $this->assertIsObject($result);
        $this->assertEquals(1, $result->return_code);
        $this->assertEquals('', $result->stdout);
        $this->assertEquals('Command not found', $result->stderr);
    }
    
    public function testParseCommand()
    {
        $method = new \ReflectionMethod(CommandRunner::class, 'parseCommand');
        $method->setAccessible(true);
        
        $result = $method->invoke($this->runner, 'test --option=value -f arg1 arg2');
        
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals('test', $result[0]);
        $this->assertIsArray($result[1]);
        $this->assertEquals('value', $result[1]['--option']);
        $this->assertTrue($result[1]['-f']);
        $this->assertEquals('arg1', $result[1][0]);
        $this->assertEquals('arg2', $result[1][1]);
    }
}
