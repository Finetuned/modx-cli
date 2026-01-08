<?php

namespace MODX\CLI\Tests\Command\Source;

use MODX\CLI\Command\Source\Update;
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
        $this->assertEquals('Source\Update', $processor);
    }

    public function testConfigureHasCorrectName()
    {
        $this->assertEquals('source:update', $this->command->getName());
    }

    public function testConfigureHasCorrectDescription()
    {
        $this->assertEquals('Update a MODX media source', $this->command->getDescription());
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
                'object' => ['id' => 1]
            ]));
        $updateResponse->method('isError')->willReturn(false);
        
        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->willReturn($updateResponse);
        
        // Execute the command
        $this->commandTester->execute([
            'id' => '1',
            '--description' => 'Updated description'
        ]);
        
        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Media source updated successfully', $output);
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
                'message' => 'Error updating media source'
            ]));
        $updateResponse->method('isError')->willReturn(true);
        
        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->willReturn($updateResponse);
        
        // Execute the command
        $this->commandTester->execute([
            'id' => '1',
            '--name' => 'Updated Name'
        ]);
        
        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Failed to update media source', $output);
        $this->assertStringContainsString('Error updating media source', $output);
    }

    public function testUpdateWithSingleOption()
    {
        // Mock Update processor
        $updateResponse = $this->getMockBuilder('MODX\\Revolution\\Processors\\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $updateResponse->method('getResponse')
            ->willReturn(json_encode(['success' => true, 'object' => ['id' => 1]]));
        $updateResponse->method('isError')->willReturn(false);
        
        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->willReturn($updateResponse);
        
        $this->commandTester->execute([
            'id' => '1',
            '--description' => 'Updated Description'
        ]);
        
        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }

    public function testExecuteWithNonExistentSource()
    {
        // Mock Get processor returning not found
        $getResponse = $this->getMockBuilder('MODX\Revolution\Processors\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $getResponse->method('getResponse')
            ->willReturn(json_encode([
                'success' => false,
                'message' => 'Media source not found'
            ]));
        $getResponse->method('isError')->willReturn(true);
        
        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->willReturn($getResponse);
        
        $this->commandTester->execute([
            'id' => '999',
            '--name' => 'Test'
        ]);
        
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Media source not found', $output);
    }
}
