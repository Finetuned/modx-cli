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

    public function testConfigureHasCorrectProcessor()
    {
        $processor = $this->getProtectedProperty($this->command, 'processor');
        $this->assertEquals('Security\Session\Remove', $processor);
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
            ->with(\MODX\Revolution\modSession::class, '999')
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
        // Mock session object
        $mockSession = $this->getMockBuilder('MODX\\Revolution\\modSession')
            ->disableOriginalConstructor()
            ->getMock();
        $mockSession->method('get')
            ->with('user')
            ->willReturn(1);
        
        // Mock user object
        $mockUser = $this->getMockBuilder('MODX\\Revolution\\modUser')
            ->disableOriginalConstructor()
            ->getMock();
        $mockUser->method('get')
            ->with('username')
            ->willReturn('testuser');
        
        // Mock getObject calls - first for session, then for user
        $this->modx->expects($this->exactly(2))
            ->method('getObject')
            ->willReturnCallback(function($class, $id) use ($mockSession, $mockUser) {
                if ($class === \MODX\Revolution\modSession::class) {
                    return $mockSession;
                }
                if ($class === \MODX\Revolution\modUser::class) {
                    return $mockUser;
                }
                return null;
            });
        
        // Mock the runProcessor method to return a successful response
        $processorResponse = $this->getMockBuilder('MODX\Revolution\Processors\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $processorResponse->method('getResponse')
            ->willReturn(json_encode([
                'success' => true
            ]));
        $processorResponse->method('isError')->willReturn(false);
        
        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->with('Security\Session\Remove')
            ->willReturn($processorResponse);
        
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
        // Mock session object
        $mockSession = $this->getMockBuilder('MODX\Revolution\modSession')
            ->disableOriginalConstructor()
            ->getMock();
        $mockSession->method('get')
            ->with('user')
            ->willReturn(null);
        
        // Mock getObject call
        $this->modx->expects($this->once())
            ->method('getObject')
            ->with(\MODX\Revolution\modSession::class, '1')
            ->willReturn($mockSession);
        
        // Mock the runProcessor method to return a failed response
        $processorResponse = $this->getMockBuilder('MODX\Revolution\Processors\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $processorResponse->method('getResponse')
            ->willReturn(json_encode([
                'success' => false,
                'message' => 'Error removing session'
            ]));
        $processorResponse->method('isError')->willReturn(true);
        
        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->willReturn($processorResponse);
        
        // Execute the command with --force to skip confirmation
        $this->commandTester->execute([
            'id' => '1',
            '--force' => true
        ]);
        
        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Failed to remove session', $output);
        $this->assertStringContainsString('Error removing session', $output);
        $this->assertEquals(1, $this->commandTester->getStatusCode());
    }

    public function testExecuteWithJsonOption()
    {
        // Mock session object
        $mockSession = $this->getMockBuilder('MODX\Revolution\modSession')
            ->disableOriginalConstructor()
            ->getMock();
        $mockSession->method('get')
            ->with('user')
            ->willReturn(null);
        
        // Mock getObject call
        $this->modx->expects($this->once())
            ->method('getObject')
            ->willReturn($mockSession);
        
        // Mock the runProcessor method to return a successful response
        $processorResponse = $this->getMockBuilder('MODX\Revolution\Processors\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $processorResponse->method('getResponse')
            ->willReturn(json_encode([
                'success' => true
            ]));
        $processorResponse->method('isError')->willReturn(false);
        
        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->willReturn($processorResponse);
        
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
    }
}
