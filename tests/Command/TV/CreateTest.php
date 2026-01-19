<?php

namespace MODX\CLI\Tests\Command\TV;

use MODX\CLI\Command\TV\Create;
//use PHPUnit\Framework\TestCase;
use MODX\CLI\Tests\Configuration\BaseTest;
use MODX\CLI\Application;
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
        $this->assertEquals('Element\Tv\Create', $processor);
    }

    public function testConfigureHasCorrectName()
    {
        $this->assertEquals('tv:create', $this->command->getName());
    }

    public function testConfigureHasCorrectDescription()
    {
        $this->assertEquals('Create a MODX template variable', $this->command->getDescription());
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
                'object' => ['id' => 123]
            ]));
        $processorResponse->method('isError')->willReturn(false);

        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->with(
                'Element\Tv\Create',
                $this->callback(function ($properties) {
                    return isset($properties['name']) && $properties['name'] === 'TestTV' &&
                           isset($properties['caption']) && $properties['caption'] === 'Test Caption' &&
                           isset($properties['description']) && $properties['description'] === 'Test description' &&
                           isset($properties['category']) && $properties['category'] === '1' &&
                           isset($properties['type']) && $properties['type'] === 'text' &&
                           isset($properties['default_text']) && $properties['default_text'] === 'Default value' &&
                           isset($properties['templates']) && $properties['templates'] === '1,2,3';
                }),
                $this->anything()
            )
            ->willReturn($processorResponse);

        // Execute the command
        $this->commandTester->execute([
            'name' => 'TestTV',
            '--caption' => 'Test Caption',
            '--description' => 'Test description',
            '--category' => '1',
            '--type' => 'text',
            '--default_text' => 'Default value',
            '--templates' => '1,2,3'
        ]);

        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Template variable created successfully', $output);
        $this->assertStringContainsString('Template variable ID: 123', $output);
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
                'message' => 'Error creating template variable'
            ]));
        $processorResponse->method('isError')->willReturn(true);

        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->willReturn($processorResponse);

        // Execute the command
        $this->commandTester->execute([
            'name' => 'TestTV'
        ]);

        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Failed to create template variable', $output);
        $this->assertStringContainsString('Error creating template variable', $output);
    }
}
