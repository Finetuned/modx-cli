<?php

namespace MODX\CLI\Tests\Command\Context\Setting;

use MODX\CLI\Command\Context\Setting\Get;
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
        $this->assertEquals('Context\Setting\Get', $processor);
    }

    public function testConfigureHasCorrectName()
    {
        $this->assertEquals('context:setting:get', $this->command->getName());
    }

    public function testConfigureHasCorrectDescription()
    {
        $this->assertEquals('Get a context setting', $this->command->getDescription());
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
                    'key' => 'site_name',
                    'value' => 'My Site',
                    'context_key' => 'web'
                ]
            ]));
        $processorResponse->method('isError')->willReturn(false);
        
        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->with(
                'Context\Setting\Get',
                $this->callback(function($properties) {
                    return isset($properties['context_key']) 
                        && $properties['context_key'] === 'web'
                        && isset($properties['key'])
                        && $properties['key'] === 'site_name';
                }),
                $this->anything()
            )
            ->willReturn($processorResponse);
        
        // Execute the command
        $this->commandTester->execute([
            'context' => 'web',
            'key' => 'site_name'
        ]);
        
        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Key: site_name', $output);
        $this->assertStringContainsString('Value: My Site', $output);
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
                'message' => 'Setting not found'
            ]));
        $processorResponse->method('isError')->willReturn(true);
        
        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->willReturn($processorResponse);
        
        // Execute the command
        $this->commandTester->execute([
            'context' => 'web',
            'key' => 'nonexistent'
        ]);
        
        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Failed to get context setting', $output);
        $this->assertStringContainsString('Setting not found', $output);
    }

    public function testExecuteWithJsonOption()
    {
        $processorResponse = $this->getMockBuilder('MODX\Revolution\Processors\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $processorResponse->method('getResponse')
            ->willReturn(json_encode([
                'success' => true,
                'object' => ['key' => 'site_name', 'value' => 'Test']
            ]));
        $processorResponse->method('isError')->willReturn(false);
        
        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->willReturn($processorResponse);
        
        $this->commandTester->execute([
            'context' => 'web',
            'key' => 'site_name',
            '--json' => true
        ]);
        
        $output = $this->commandTester->getDisplay();
        $data = json_decode($output, true);
        
        $this->assertIsArray($data);
        $this->assertArrayHasKey('success', $data);
        $this->assertTrue($data['success']);
    }
}