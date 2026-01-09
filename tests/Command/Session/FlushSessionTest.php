<?php

namespace MODX\CLI\Tests\Command\Session;

use MODX\CLI\Command\Session\FlushSession;
use MODX\CLI\Tests\Configuration\BaseTest;
use Symfony\Component\Console\Tester\CommandTester;

class FlushSessionTest extends BaseTest
{
    protected $command;
    protected $commandTester;
    protected $modx;
    protected $services;

    protected function setUp(): void
    {
        // Create a mock MODX object
        $this->modx = $this->createMock('MODX\Revolution\modX');

        $this->services = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['has', 'add'])
            ->getMock();
        $this->services->method('has')
            ->with('session_handler')
            ->willReturn(true);
        $this->modx->services = $this->services;
        
        // Create the command
        $this->command = new FlushSession();
        $this->command->modx = $this->modx;
        
        // Create a command tester
        $this->commandTester = new CommandTester($this->command);
    }

    public function testConfigureHasCorrectProcessor()
    {
        $processor = $this->getProtectedProperty($this->command, 'processor');
        $this->assertEquals('Security\\Flush', $processor);
    }

    public function testConfigureHasCorrectName()
    {
        $this->assertEquals('session:flush', $this->command->getName());
    }

    public function testConfigureHasCorrectDescription()
    {
        $this->assertEquals('Flush all sessions in MODX', $this->command->getDescription());
    }

    public function testConfigureHasForceOption()
    {
        $definition = $this->command->getDefinition();
        $this->assertTrue($definition->hasOption('force'));
        
        $option = $definition->getOption('force');
        $this->assertEquals('f', $option->getShortcut());
        $this->assertFalse($option->isValueRequired());
    }

    public function testExecuteWithSuccessfulResponse()
    {
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
            ->with('Security\\Flush')
            ->willReturn($processorResponse);
        
        // Execute the command with --force to skip confirmation
        $this->commandTester->execute(['--force' => true]);
        
        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Sessions flushed successfully', $output);
        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }

    public function testExecuteWithFailedResponse()
    {
        // Mock the runProcessor method to return a failed response
        $processorResponse = $this->getMockBuilder('MODX\Revolution\Processors\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $processorResponse->method('getResponse')
            ->willReturn(json_encode([
                'success' => false,
                'message' => 'Error flushing sessions'
            ]));
        $processorResponse->method('isError')->willReturn(true);
        
        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->willReturn($processorResponse);
        
        // Execute the command with --force to skip confirmation
        $this->commandTester->execute(['--force' => true]);
        
        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Failed to flush sessions', $output);
        $this->assertStringContainsString('Error flushing sessions', $output);
        $this->assertEquals(1, $this->commandTester->getStatusCode());
    }

    public function testExecuteWithJsonOption()
    {
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
        $this->commandTester->execute(['--force' => true, '--json' => true]);
        
        // Verify the output is valid JSON
        $output = $this->commandTester->getDisplay();
        $decoded = json_decode($output, true);
        $this->assertIsArray($decoded);
        $this->assertTrue($decoded['success']);
    }
}
