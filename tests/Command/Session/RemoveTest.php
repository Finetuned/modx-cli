<?php

namespace MODX\CLI\Tests\Command\Session;

use MODX\CLI\Command\Session\Remove;
use MODX\CLI\Tests\Configuration\BaseTest;
use Symfony\Component\Console\Tester\CommandTester;

class RemoveTest extends BaseTest
{
    protected $command;
    protected $commandTester;
    protected $modx;

    protected function setUp(): void
    {
        // Create a mock MODX object
        $this->modx = $this->createMock('MODX\Revolution\modX');
        
        // Create the command
        $this->command = new Remove();
        $this->command->modx = $this->modx;
        
        // Create a command tester
        $this->commandTester = new CommandTester($this->command);
    }

    public function testConfigureHasCorrectName()
    {
        $this->assertEquals('session:remove', $this->command->getName());
    }

    public function testConfigureHasCorrectDescription()
    {
        $this->assertEquals('Remove a session in MODX', $this->command->getDescription());
    }

    public function testConfigureHasIdArgument()
    {
        $definition = $this->command->getDefinition();
        $this->assertTrue($definition->hasArgument('id'));
        
        $argument = $definition->getArgument('id');
        $this->assertTrue($argument->isRequired());
    }

    public function testConfigureHasForceOption()
    {
        $definition = $this->command->getDefinition();
        $this->assertTrue($definition->hasOption('force'));
        
        $option = $definition->getOption('force');
        $this->assertEquals('f', $option->getShortcut());
        $this->assertFalse($option->isValueRequired());
    }

    public function testExecuteWithNonExistentSession()
    {
        // Mock getObject to return null (session not found)
        $this->modx->expects($this->once())
            ->method('getObject')
            ->with('MODX\\Revolution\\modActiveUser', ['internalKey' => '999'])
            ->willReturn(null);
        
        // Execute the command with --force to skip confirmation
        $this->commandTester->execute([
            'id' => '999',
            '--force' => true
        ]);
        
        // Verify the error output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Session with ID 999 not found', $output);
    }

    public function testExecuteWithSuccessfulResponse()
    {
        // Mock modActiveUser object
        $mockActiveUser = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['get', 'remove'])
            ->getMock();
        $mockActiveUser->method('get')
            ->with('username')
            ->willReturn('testuser');
        $mockActiveUser->method('remove')
            ->willReturn(true);
        
        // Mock getObject call
        $this->modx->expects($this->once())
            ->method('getObject')
            ->with('MODX\\Revolution\\modActiveUser', ['internalKey' => '1'])
            ->willReturn($mockActiveUser);
        
        // Execute the command with --force to skip confirmation
        $this->commandTester->execute([
            'id' => '1',
            '--force' => true
        ]);
        
        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Session removed successfully', $output);
        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }

    public function testExecuteWithFailedResponse()
    {
        // Mock modActiveUser object that fails to remove
        $mockActiveUser = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['get', 'remove'])
            ->getMock();
        $mockActiveUser->method('get')
            ->with('username')
            ->willReturn('testuser');
        $mockActiveUser->method('remove')
            ->willReturn(false);
        
        // Mock getObject call
        $this->modx->expects($this->once())
            ->method('getObject')
            ->with('MODX\\Revolution\\modActiveUser', ['internalKey' => '1'])
            ->willReturn($mockActiveUser);
        
        // Execute the command with --force to skip confirmation
        $this->commandTester->execute([
            'id' => '1',
            '--force' => true
        ]);
        
        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Failed to remove session', $output);
        $this->assertEquals(1, $this->commandTester->getStatusCode());
    }

    public function testExecuteWithJsonOption()
    {
        // Mock modActiveUser object
        $mockActiveUser = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['get', 'remove'])
            ->getMock();
        $mockActiveUser->method('get')
            ->with('username')
            ->willReturn('testuser');
        $mockActiveUser->method('remove')
            ->willReturn(true);
        
        // Mock getObject call
        $this->modx->expects($this->once())
            ->method('getObject')
            ->with('MODX\\Revolution\\modActiveUser', ['internalKey' => '1'])
            ->willReturn($mockActiveUser);
        
        // Execute the command with --force and --json
        $this->commandTester->execute([
            'id' => '1',
            '--force' => true,
            '--json' => true
        ]);
        
        // Verify the output is valid JSON
        $output = $this->commandTester->getDisplay();
        $decoded = json_decode($output, true);
        $this->assertIsArray($decoded);
        $this->assertTrue($decoded['success']);
        $this->assertEquals('Session removed successfully', $decoded['message']);
    }
}
