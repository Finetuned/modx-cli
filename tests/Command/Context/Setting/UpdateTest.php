<?php

namespace MODX\CLI\Tests\Command\Context\Setting;

use MODX\CLI\Command\Context\Setting\Update;
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
        $this->assertEquals('Context\Setting\Update', $processor);
    }

    public function testConfigureHasCorrectName()
    {
        $this->assertEquals('context:setting:update', $this->command->getName());
    }

    public function testConfigureHasCorrectDescription()
    {
        $this->assertEquals('Update a context setting', $this->command->getDescription());
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
                'object' => ['key' => 'site_name']
            ]));
        $updateResponse->method('isError')->willReturn(false);
        
        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->willReturn($updateResponse);
        
        // Execute the command
        $this->commandTester->execute([
            'context' => 'web',
            'key' => 'site_name',
            '--value' => 'New Site Name'
        ]);
        
        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Context setting updated successfully', $output);
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
                'message' => 'Error updating context setting'
            ]));
        $updateResponse->method('isError')->willReturn(true);
        
        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->willReturn($updateResponse);
        
        // Execute the command
        $this->commandTester->execute([
            'context' => 'web',
            'key' => 'site_name',
            '--value' => 'New Value'
        ]);
        
        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Failed to update context setting', $output);
        $this->assertStringContainsString('Error updating context setting', $output);
    }

    public function testUpdateWithSingleOption()
    {
        // Mock Update processor
        $updateResponse = $this->getMockBuilder('MODX\\Revolution\\Processors\\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $updateResponse->method('getResponse')
            ->willReturn(json_encode(['success' => true, 'object' => ['key' => 'site_name']]));
        $updateResponse->method('isError')->willReturn(false);
        
        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->willReturn($updateResponse);
        
        $this->commandTester->execute([
            'context' => 'web',
            'key' => 'site_name',
            '--value' => 'Updated Value'
        ]);
        
        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }

    public function testExecuteWithNonExistentSetting()
    {
        // Mock Get processor returning not found
        $getResponse = $this->getMockBuilder('MODX\Revolution\Processors\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $getResponse->method('getResponse')
            ->willReturn(json_encode([
                'success' => false,
                'message' => 'Setting not found'
            ]));
        $getResponse->method('isError')->willReturn(true);
        
        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->willReturn($getResponse);
        
        $this->commandTester->execute([
            'context' => 'web',
            'key' => 'nonexistent',
            '--value' => 'Test'
        ]);
        
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Setting not found', $output);
    }
}        
