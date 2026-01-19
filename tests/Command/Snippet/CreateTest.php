<?php

namespace MODX\CLI\Tests\Command\Snippet;

use MODX\CLI\Command\Snippet\Create;
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
        $this->assertEquals('Element\Snippet\Create', $processor);
    }

    public function testConfigureHasCorrectName()
    {
        $this->assertEquals('snippet:create', $this->command->getName());
    }

    public function testConfigureHasCorrectDescription()
    {
        $this->assertEquals('Create a MODX snippet', $this->command->getDescription());
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
                'Element\Snippet\Create',
                $this->callback(function ($properties) {
                    return isset($properties['name']) && $properties['name'] === 'TestSnippet' &&
                           isset($properties['description']) && $properties['description'] === 'Test description' &&
                           isset($properties['category']) && $properties['category'] === '1' &&
                           isset($properties['snippet']) && $properties['snippet'] === '<?php return "Hello World!";';
                }),
                $this->anything()
            )
            ->willReturn($processorResponse);

        // Execute the command
        $this->commandTester->execute([
            'name' => 'TestSnippet',
            '--description' => 'Test description',
            '--category' => '1',
            '--snippet' => '<?php return "Hello World!";'
        ]);

        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Snippet created successfully', $output);
        $this->assertStringContainsString('Snippet ID: 123', $output);
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
                'message' => 'Error creating snippet'
            ]));
        $processorResponse->method('isError')->willReturn(true);

        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->willReturn($processorResponse);

        // Execute the command
        $this->commandTester->execute([
            'name' => 'TestSnippet'
        ]);

        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Failed to create snippet', $output);
        $this->assertStringContainsString('Error creating snippet', $output);
    }
}
