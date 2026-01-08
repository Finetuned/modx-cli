<?php

namespace MODX\CLI\Tests\Command\Source;

use MODX\CLI\Command\Source\Get;
use MODX\CLI\Tests\Configuration\BaseTest;
use Symfony\Component\Console\Tester\CommandTester;

class GetTest extends BaseTest
{
    protected $command;
    protected $commandTester;
    protected $modx;

    protected function setUp(): void
    {
        // Create a mock MODX object
        $this->modx = $this->createMock('MODX\Revolution\modX');
        
        // Create the command
        $this->command = new Get();
        $this->command->modx = $this->modx;
        
        // Create a command tester
        $this->commandTester = new CommandTester($this->command);
    }

    public function testConfigureHasCorrectProcessorPath()
    {
        $processor = $this->getProtectedProperty($this->command, 'processor');
        $this->assertEquals('Source\Get', $processor);
    }

    public function testConfigureHasCorrectName()
    {
        $this->assertEquals('source:get', $this->command->getName());
    }

    public function testConfigureHasCorrectDescription()
    {
        $this->assertEquals('Get a MODX media source by ID', $this->command->getDescription());
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
                'object' => [
                    'id' => 1,
                    'name' => 'Filesystem',
                    'description' => 'Default filesystem source',
                    'class_key' => 'MODX\\Revolution\\Sources\\modFileMediaSource'
                ]
            ]));
        $processorResponse->method('isError')->willReturn(false);
        
        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->with(
                'Source\Get',
                $this->callback(function($properties) {
                    return isset($properties['id']) && $properties['id'] === '1';
                }),
                $this->anything()
            )
            ->willReturn($processorResponse);
        
        // Execute the command
        $this->commandTester->execute([
            'id' => '1'
        ]);
        
        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('ID: 1', $output);
        $this->assertStringContainsString('Name: Filesystem', $output);
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
                'message' => 'Media source not found'
            ]));
        $processorResponse->method('isError')->willReturn(true);
        
        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->willReturn($processorResponse);
        
        // Execute the command
        $this->commandTester->execute([
            'id' => '999'
        ]);
        
        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Failed to get media source', $output);
        $this->assertStringContainsString('Media source not found', $output);
    }

    public function testExecuteWithJsonOption()
    {
        $processorResponse = $this->getMockBuilder('MODX\Revolution\Processors\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $processorResponse->method('getResponse')
            ->willReturn(json_encode([
                'success' => true,
                'object' => ['id' => 1, 'name' => 'Filesystem']
            ]));
        $processorResponse->method('isError')->willReturn(false);
        
        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->willReturn($processorResponse);
        
        $this->commandTester->execute([
            'id' => '1',
            '--json' => true
        ]);
        
        $output = $this->commandTester->getDisplay();
        $data = json_decode($output, true);
        
        $this->assertIsArray($data);
        $this->assertArrayHasKey('success', $data);
        $this->assertTrue($data['success']);
    }
}