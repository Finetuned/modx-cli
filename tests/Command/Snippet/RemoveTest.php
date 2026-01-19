<?php

namespace MODX\CLI\Tests\Command\Snippet;

use MODX\CLI\Command\Snippet\Remove;
//use PHPUnit\Framework\TestCase;
use MODX\CLI\Tests\Configuration\BaseTest;
use MODX\CLI\Application;
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
        $this->assertEquals('Element\Snippet\Remove', $processor);
    }

    public function testConfigureHasCorrectName()
    {
        $this->assertEquals('snippet:remove', $this->command->getName());
    }

    public function testConfigureHasCorrectDescription()
    {
        $this->assertEquals('Remove a MODX snippet', $this->command->getDescription());
    }

    public function testExecuteWithSuccessfulResponse()
    {
        // Mock the snippet object
        $snippet = $this->getMockBuilder('MODX\Revolution\modSnippet')
            ->disableOriginalConstructor()
            ->getMock();
        $snippet->method('get')
            ->with('name')
            ->willReturn('Test Snippet');

        // Mock the getObject method
        $this->modx->method('getObject')
            ->with(\MODX\Revolution\modSnippet::class, '123', $this->anything())
            ->willReturn($snippet);

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
                'Element\Snippet\Remove',
                $this->callback(function ($properties) {
                    return isset($properties['id']) && $properties['id'] === '123';
                }),
                $this->anything()
            )
            ->willReturn($processorResponse);

        // Execute the command with force option
        $this->commandTester->execute([
            'id' => '123',
            '--force' => true
        ]);

        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Snippet removed successfully', $output);
    }

    public function testExecuteWithSnippetNotFound()
    {
        // Mock the getObject method to return null (snippet not found)
        $this->modx->method('getObject')
            ->with(\MODX\Revolution\modSnippet::class, '999', $this->anything())
            ->willReturn(null);

        // Execute the command
        $this->commandTester->execute([
            'id' => '999'
        ]);

        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Snippet with ID 999 not found', $output);
    }

    public function testExecuteWithFailedResponse()
    {
        // Mock the snippet object
        $snippet = $this->getMockBuilder('MODX\Revolution\modSnippet')
            ->disableOriginalConstructor()
            ->getMock();
        $snippet->method('get')
            ->with('name')
            ->willReturn('Test Snippet');

        // Mock the getObject method
        $this->modx->method('getObject')
            ->with(\MODX\Revolution\modSnippet::class, '123', $this->anything())
            ->willReturn($snippet);

        // Mock the runProcessor method to return a failed response
        $processorResponse = $this->getMockBuilder('MODX\Revolution\Processors\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $processorResponse->method('getResponse')
            ->willReturn(json_encode([
                'success' => false,
                'message' => 'Error removing snippet'
            ]));
        $processorResponse->method('isError')->willReturn(true);

        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->willReturn($processorResponse);

        // Execute the command with force option
        $this->commandTester->execute([
            'id' => '123',
            '--force' => true
        ]);

        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Failed to remove snippet', $output);
        $this->assertStringContainsString('Error removing snippet', $output);
    }
}
