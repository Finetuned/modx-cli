<?php

namespace MODX\CLI\Tests\Command\Plugin;

use MODX\CLI\Command\Plugin\GetList;
use MODX\CLI\Tests\Configuration\BaseTest;
use MODX\CLI\Application;
use Symfony\Component\Console\Tester\CommandTester;

class GetListTest extends BaseTest
{
    protected $command;
    protected $commandTester;
    protected $modx;

    protected function setUp(): void
    {
        // Create a mock MODX object
        $this->modx = $this->createMock('MODX\Revolution\modX');

        // Create the command
        $this->command = new GetList();
        $this->command->modx = $this->modx;

        // Create a command tester
        $this->commandTester = new CommandTester($this->command);
    }

    public function testConfigureHasCorrectProcessorPath()
    {
        $processor = $this->getProtectedProperty($this->command, 'processor');
        $this->assertEquals('Element\Plugin\GetList', $processor);
    }

    public function testConfigureHasCorrectName()
    {
        $this->assertEquals('plugin:list', $this->command->getName());
    }

    public function testConfigureHasCorrectDescription()
    {
        $this->assertEquals('Get a list of plugins in MODX', $this->command->getDescription());
    }

    public function testConfigureHasCorrectHeaders()
    {
        $headers = $this->getProtectedProperty($this->command, 'headers');
        $this->assertIsArray($headers);
        $this->assertContains('id', $headers);
        $this->assertContains('name', $headers);
        $this->assertContains('description', $headers);
        $this->assertContains('category', $headers);
        $this->assertContains('disabled', $headers);
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
                'results' => [
                    ['id' => 1, 'name' => 'TestPlugin', 'description' => 'A test plugin', 'category' => 1, 'disabled' => 0],
                    ['id' => 2, 'name' => 'AnotherPlugin', 'description' => 'Another test plugin', 'category' => 1, 'disabled' => 1],
                ]
            ]));
        $processorResponse->method('isError')->willReturn(false);

        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->with('Element\Plugin\GetList')
            ->willReturn($processorResponse);

        // Execute the command
        $this->commandTester->execute([

        ]);

        // Verify the output contains plugin data
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('TestPlugin', $output);
        $this->assertStringContainsString('AnotherPlugin', $output);
    }
}
