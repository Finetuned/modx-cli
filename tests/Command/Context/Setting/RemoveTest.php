<?php

namespace MODX\CLI\Tests\Command\Context\Setting;

use MODX\CLI\Command\Context\Setting\Remove;
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

    public function testConfigureHasCorrectProcessorPath()
    {
        $processor = $this->getProtectedProperty($this->command, 'processor');
        $this->assertEquals('Context\Setting\Remove', $processor);
    }

    public function testConfigureHasCorrectName()
    {
        $this->assertEquals('context:setting:remove', $this->command->getName());
    }

    public function testConfigureHasCorrectDescription()
    {
        $this->assertEquals('Remove a context setting', $this->command->getDescription());
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
            ->with(
                'Context\Setting\Remove',
                $this->callback(function($properties) {
                    return isset($properties['context_key']) 
                        && $properties['context_key'] === 'web'
                        && isset($properties['key'])
                        && $properties['key'] === 'custom_setting';
                }),
                $this->anything()
            )
            ->willReturn($processorResponse);
        
        // Execute the command with --force to skip confirmation
        $this->commandTester->execute([
            'context' => 'web',
            'key' => 'custom_setting',
            '--force' => true
        ]);
        
        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Context setting removed successfully', $output);
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
                'message' => 'Cannot remove system setting'
            ]));
        $processorResponse->method('isError')->willReturn(true);
        
        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->willReturn($processorResponse);
        
        // Execute the command with --force
        $this->commandTester->execute([
            'context' => 'web',
            'key' => 'site_name',
            '--force' => true
        ]);
        
        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Failed to remove context setting', $output);
        $this->assertStringContainsString('Cannot remove system setting', $output);
    }

    public function testForceOptionSkipsConfirmation()
    {
        $processorResponse = $this->getMockBuilder('MODX\Revolution\Processors\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $processorResponse->method('getResponse')
            ->willReturn(json_encode(['success' => true]));
        $processorResponse->method('isError')->willReturn(false);
        
        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->willReturn($processorResponse);
        
        $this->commandTester->execute([
            'context' => 'web',
            'key' => 'custom_setting',
            '--force' => true
        ]);
        
        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }
}