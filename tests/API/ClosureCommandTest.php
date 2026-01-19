<?php

namespace MODX\CLI\Tests\API;

use MODX\CLI\API\ClosureCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class ClosureCommandTest extends TestCase
{
    public function testExecuteWithSimpleClosure()
    {
        $name = 'test:closure';
        $closure = function ($args, $assoc_args) {
            return 0;
        };

        $command = new ClosureCommand($name, $closure);

        $input = new ArrayInput([]);
        $output = new BufferedOutput();

        $result = $command->run($input, $output);

        $this->assertEquals(0, $result);
    }

    public function testExecuteWithReturnValue()
    {
        $name = 'test:return-value';
        $closure = function ($args, $assoc_args) {
            return 42;
        };

        $command = new ClosureCommand($name, $closure);

        $input = new ArrayInput([]);
        $output = new BufferedOutput();

        $result = $command->run($input, $output);

        $this->assertEquals(42, $result);
    }

    public function testExecuteWithArguments()
    {
        $name = 'test:arguments';
        $closure = function ($args, $assoc_args) {
            return count($args);
        };

        $command = new ClosureCommand($name, $closure);

        // Configure the command to accept arguments
        $command->addArgument('arg1', \Symfony\Component\Console\Input\InputArgument::OPTIONAL);
        $command->addArgument('arg2', \Symfony\Component\Console\Input\InputArgument::OPTIONAL);

        $input = new ArrayInput([
            'arg1' => 'value1',
            'arg2' => 'value2'
        ]);
        $output = new BufferedOutput();

        $result = $command->run($input, $output);

        $this->assertEquals(2, $result);
    }

    public function testExecuteWithOptions()
    {
        $name = 'test:options';
        $closure = function ($args, $assoc_args) {
            return isset($assoc_args['option']) ? 1 : 0;
        };

        $command = new ClosureCommand($name, $closure);

        // Configure the command to accept options
        $command->addOption('option', null, \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL);

        $input = new ArrayInput([
            '--option' => 'value'
        ]);
        $output = new BufferedOutput();

        $result = $command->run($input, $output);

        $this->assertEquals(1, $result);
    }

    public function testBeforeInvokeHook()
    {
        $name = 'test:before-invoke';
        $beforeInvokeCalled = false;

        $closure = function ($args, $assoc_args) {
            return 0;
        };

        $beforeInvoke = function () use (&$beforeInvokeCalled) {
            $beforeInvokeCalled = true;
        };

        $command = new ClosureCommand($name, $closure);
        $command->setBeforeInvoke($beforeInvoke);

        $input = new ArrayInput([]);
        $output = new BufferedOutput();

        $command->run($input, $output);

        $this->assertTrue($beforeInvokeCalled);
    }

    public function testAfterInvokeHook()
    {
        $name = 'test:after-invoke';
        $afterInvokeCalled = false;
        $commandResult = null;

        $closure = function ($args, $assoc_args) {
            return 42;
        };

        $afterInvoke = function ($input, $output, $result) use (&$afterInvokeCalled, &$commandResult) {
            $afterInvokeCalled = true;
            $commandResult = $result;
        };

        $command = new ClosureCommand($name, $closure);
        $command->setAfterInvoke($afterInvoke);

        $input = new ArrayInput([]);
        $output = new BufferedOutput();

        $command->run($input, $output);

        $this->assertTrue($afterInvokeCalled);
        $this->assertEquals(42, $commandResult);
    }

    public function testBothHooks()
    {
        $name = 'test:both-hooks';
        $beforeInvokeCalled = false;
        $afterInvokeCalled = false;

        $closure = function ($args, $assoc_args) {
            return 0;
        };

        $beforeInvoke = function () use (&$beforeInvokeCalled) {
            $beforeInvokeCalled = true;
        };

        $afterInvoke = function () use (&$afterInvokeCalled) {
            $afterInvokeCalled = true;
        };

        $command = new ClosureCommand($name, $closure);
        $command->setBeforeInvoke($beforeInvoke);
        $command->setAfterInvoke($afterInvoke);

        $input = new ArrayInput([]);
        $output = new BufferedOutput();

        $command->run($input, $output);

        $this->assertTrue($beforeInvokeCalled);
        $this->assertTrue($afterInvokeCalled);
    }

    public function testOutputCapture()
    {
        $name = 'test:output';
        $closure = function ($args, $assoc_args, $input, $output) {
            $output->writeln('Hello, World!');
            return 0;
        };

        $command = new ClosureCommand($name, $closure);

        $input = new ArrayInput([]);
        $output = new BufferedOutput();

        $command->run($input, $output);

        $this->assertEquals("Hello, World!" . PHP_EOL, $output->fetch());
    }

    public function testNonIntegerReturnValue()
    {
        $name = 'test:non-integer-return';
        $closure = function ($args, $assoc_args) {
            return 'not an integer';
        };

        $command = new ClosureCommand($name, $closure);

        $input = new ArrayInput([]);
        $output = new BufferedOutput();

        $result = $command->run($input, $output);

        $this->assertEquals(0, $result); // Should default to 0 for non-integer returns
    }

    // NEW TDD TESTS FOR ARGUMENT CONFLICT FIX
    public function testClosureCommandDoesNotAddCommandArgument()
    {
        $closure = function ($args, $options) {
            return 0;
        };
        $command = new ClosureCommand('test:command', $closure);

        // Should NOT have a manually added 'command' argument
        $definition = $command->getDefinition();
        $this->assertFalse($definition->hasArgument('command'));
    }

    public function testClosureCommandExecutesWithoutArgumentConflict()
    {
        $executed = false;
        $closure = function ($args, $options) use (&$executed) {
            $executed = true;
            return 0;
        };

        $command = new ClosureCommand('test:command', $closure);

        // Should execute without throwing "argument already exists" error
        $input = new ArrayInput([]);
        $output = new BufferedOutput();

        $result = $command->run($input, $output);
        $this->assertEquals(0, $result);
        $this->assertTrue($executed);
    }
}
