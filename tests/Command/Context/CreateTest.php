<?php

namespace MODX\CLI\Tests\Command\Context;

use MODX\CLI\Command\Context\Create;
use MODX\CLI\Tests\Configuration\BaseTest;
use Symfony\Component\Console\Tester\CommandTester;

class CreateTest extends BaseTest
{
    protected $command;
    protected $commandTester;
    protected $modx;

    protected function setUp(): void
    {
        // Create a mock MODX object
        $this->modx = $this->createMock('MODX\Revolution\modX');
        
        // Create the command
        $this->command = new Create();
        $this->command->modx = $this->modx;
        
        // Create a command tester
        $this->commandTester = new CommandTester($this->command);
    }

    public function testConfigureHasCorrectProcessorPath()
    {
        $processor = $this->getProtectedProperty($this->command, 'processor');
        $this->assertEquals('Context\Create', $processor);
    }

    public function testConfigureHasCorrectName()
    {
        $this->assertEquals('context:create', $this->command->getName());
    }

    public function testConfigureHasCorrectDescription()
    {
        $this->assertEquals('Create a MODX context', $this->command->getDescription());
    }

    public function testExecuteWithSuccessfulResponse()
    {
        // Mock the runProcessor method to return a successful response
        $processorResponse = $this->getMockBuilder('MODX\Revolution\Processors\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $processorResponse->method('getResponse')
            ->willReturn(json_encode([
                'success' => true,
                'object' => ['key' => 'testcontext']
            ]));
        $processorResponse->method('isError')->willReturn(false);
        
        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->with(
                'Context\Create',
                $this->callback(function($properties) {
                    return isset($properties['key']) && $properties['key'] === 'testcontext';
                }),
                $this->anything()
            )
            ->willReturn($processorResponse);
        
        // Execute the command
        $this->commandTester->execute([
            'key' => 'testcontext',
            '--name' => 'Test Context',
            '--description' => 'Test description'
        ]);
        
        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Context created successfully', $output);
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
                'message' => 'Error creating context'
            ]));
        $processorResponse->method('isError')->willReturn(true);
        
        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->willReturn($processorResponse);
        
        // Execute the command
        $this->commandTester->execute([
            'key' => 'testcontext'
        ]);
        
        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Failed to create context', $output);
        $this->assertStringContainsString('Error creating context', $output);
    }

    public function testBeforeRunPopulatesProperties()
    {
        $processorResponse = $this->getMockBuilder('MODX\Revolution\Processors\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $processorResponse->method('getResponse')
            ->willReturn(json_encode(['success' => true, 'object' => ['key' => 'testcontext']]));
        $processorResponse->method('isError')->willReturn(false);
        
        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->with(
                'Context\Create',
                $this->callback(function($properties) {
                    return isset($properties['key']) 
                        && $properties['key'] === 'testcontext'
                        && isset($properties['name'])
                        && $properties['name'] === 'Test Name'
                        && isset($properties['description'])
                        && $properties['description'] === 'Test Description';
                }),
                $this->anything()
            )
            ->willReturn($processorResponse);
        
        $this->commandTester->execute([
            'key' => 'testcontext',
            '--name' => 'Test Name',
            '--description' => 'Test Description'
        ]);
        
        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }
}