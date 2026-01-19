<?php

namespace MODX\CLI\Tests\Command\Source;

use MODX\CLI\Command\Source\Create;
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
        $this->assertEquals('Source\Create', $processor);
    }

    public function testConfigureHasCorrectName()
    {
        $this->assertEquals('source:create', $this->command->getName());
    }

    public function testConfigureHasCorrectDescription()
    {
        $this->assertEquals('Create a MODX media source', $this->command->getDescription());
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
                'object' => ['id' => 123, 'name' => 'TestSource']
            ]));
        $processorResponse->method('isError')->willReturn(false);

        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->with(
                'Source\Create',
                $this->callback(function ($properties) {
                    return isset($properties['name']) && $properties['name'] === 'TestSource';
                }),
                $this->anything()
            )
            ->willReturn($processorResponse);

        // Execute the command
        $this->commandTester->execute([
            'name' => 'TestSource',
            '--description' => 'Test media source',
            '--class_key' => 'MODX\\Revolution\\Sources\\modFileMediaSource'
        ]);

        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Media source created successfully', $output);
        $this->assertStringContainsString('Source ID: 123', $output);
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
                'message' => 'Error creating media source'
            ]));
        $processorResponse->method('isError')->willReturn(true);

        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->willReturn($processorResponse);

        // Execute the command
        $this->commandTester->execute([
            'name' => 'TestSource'
        ]);

        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Failed to create media source', $output);
        $this->assertStringContainsString('Error creating media source', $output);
    }

    public function testBeforeRunPopulatesProperties()
    {
        $processorResponse = $this->getMockBuilder('MODX\Revolution\Processors\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $processorResponse->method('getResponse')
            ->willReturn(json_encode(['success' => true, 'object' => ['id' => 1]]));
        $processorResponse->method('isError')->willReturn(false);

        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->with(
                'Source\Create',
                $this->callback(function ($properties) {
                    return isset($properties['name'])
                        && $properties['name'] === 'TestSource'
                        && isset($properties['description'])
                        && $properties['description'] === 'Test Description';
                }),
                $this->anything()
            )
            ->willReturn($processorResponse);

        $this->commandTester->execute([
            'name' => 'TestSource',
            '--description' => 'Test Description'
        ]);

        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }
}
