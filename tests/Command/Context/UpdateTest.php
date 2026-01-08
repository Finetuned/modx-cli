<?php

namespace MODX\CLI\Tests\Command\Context;

use MODX\CLI\Command\Context\Update;
use MODX\CLI\Tests\Configuration\BaseTest;
use Symfony\Component\Console\Tester\CommandTester;

class UpdateTest extends BaseTest
{
    protected $command;
    protected $commandTester;
    protected $modx;

    protected function setUp(): void
    {
        // Create a mock MODX object
        $this->modx = $this->createMock('MODX\Revolution\modX');
        
        // Create the command
        $this->command = new Update();
        $this->command->modx = $this->modx;
        
        // Create a command tester
        $this->commandTester = new CommandTester($this->command);
    }

    public function testConfigureHasCorrectProcessorPath()
    {
        $processor = $this->getProtectedProperty($this->command, 'processor');
        $this->assertEquals('Context\Update', $processor);
    }

    public function testConfigureHasCorrectName()
    {
        $this->assertEquals('context:update', $this->command->getName());
    }

    public function testConfigureHasCorrectDescription()
    {
        $this->assertEquals('Update a MODX context', $this->command->getDescription());
    }

    public function testExecuteWithSuccessfulResponse()
    {
        // Mock Update processor
        $updateResponse = $this->getMockBuilder('MODX\\Revolution\\Processors\\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $updateResponse->method('getResponse')
            ->willReturn(json_encode([
                'success' => true,
                'object' => ['key' => 'web']
            ]));
        $updateResponse->method('isError')->willReturn(false);
        
        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->willReturn($updateResponse);
        
        // Execute the command
        $this->commandTester->execute([
            'key' => 'web',
            '--description' => 'Updated description'
        ]);
        
        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Context updated successfully', $output);
    }

    public function testExecuteWithFailedResponse()
    {
        // Mock failed Update processor
        $updateResponse = $this->getMockBuilder('MODX\\Revolution\\Processors\\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $updateResponse->method('getResponse')
            ->willReturn(json_encode([
                'success' => false,
                'message' => 'Error updating context'
            ]));
        $updateResponse->method('isError')->willReturn(true);
        
        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->willReturn($updateResponse);
        
        // Execute the command
        $this->commandTester->execute([
            'key' => 'web',
            '--name' => 'Updated Name'
        ]);
        
        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Failed to update context', $output);
        $this->assertStringContainsString('Error updating context', $output);
    }

    public function testUpdateWithSingleOption()
    {
        // Mock Update processor
        $updateResponse = $this->getMockBuilder('MODX\\Revolution\\Processors\\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $updateResponse->method('getResponse')
            ->willReturn(json_encode(['success' => true, 'object' => ['key' => 'web']]));
        $updateResponse->method('isError')->willReturn(false);
        
        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->willReturn($updateResponse);
        
        $this->commandTester->execute([
            'key' => 'web',
            '--rank' => '5'
        ]);
        
        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }

    public function testExecuteWithNonExistentContext()
    {
        // Mock Get processor returning not found
        $getResponse = $this->getMockBuilder('MODX\Revolution\Processors\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $getResponse->method('getResponse')
            ->willReturn(json_encode([
                'success' => false,
                'message' => 'Context not found'
            ]));
        $getResponse->method('isError')->willReturn(true);
        
        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->willReturn($getResponse);
        
        $this->commandTester->execute([
            'key' => 'nonexistent',
            '--name' => 'Test'
        ]);
        
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Context not found', $output);
    }
}
