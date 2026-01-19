<?php

namespace MODX\CLI\Tests\Command\Ns;

use MODX\CLI\Command\Ns\Update;
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
        $this->assertEquals('Workspace\PackageNamespace\Update', $processor);
    }

    public function testConfigureHasCorrectName()
    {
        $this->assertEquals('ns:update', $this->command->getName());
    }

    public function testConfigureHasCorrectDescription()
    {
        $this->assertEquals('Update a namespace in MODX', $this->command->getDescription());
    }

    public function testExecuteWithSuccessfulUpdate()
    {
        // Mock the runProcessor method to return a successful response
        $processorResponse = $this->getMockBuilder('MODX\Revolution\Processors\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();

        $processorResponse->method('getResponse')
            ->willReturn(json_encode([
                'success' => true,
                'object' => ['id' => 123, 'name' => 'testnamespace']
            ]));
        $processorResponse->method('isError')->willReturn(false);

        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->with(
                'Workspace\PackageNamespace\Update',
                $this->callback(function ($properties) {
                    return isset($properties['name']) && $properties['name'] === 'testnamespace';
                }),
                $this->anything()
            )
            ->willReturn($processorResponse);

        // Execute the command
        $this->commandTester->execute([
            'name' => 'testnamespace'
        ]);

        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Namespace updated successfully', $output);
        $this->assertStringContainsString('Namespace ID: 123', $output);
    }

    public function testExecuteWithPathUpdate()
    {
        // Mock the runProcessor method
        $processorResponse = $this->getMockBuilder('MODX\Revolution\Processors\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();

        $processorResponse->method('getResponse')
            ->willReturn(json_encode([
                'success' => true,
                'object' => ['id' => 456]
            ]));
        $processorResponse->method('isError')->willReturn(false);

        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->with(
                'Workspace\PackageNamespace\Update',
                $this->callback(function ($properties) {
                    return isset($properties['name']) && $properties['name'] === 'myns' &&
                           isset($properties['path']) && $properties['path'] === '/updated/path/';
                }),
                $this->anything()
            )
            ->willReturn($processorResponse);

        // Execute the command with path option
        $this->commandTester->execute([
            'name' => 'myns',
            '--path' => '/updated/path/'
        ]);

        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }

    public function testExecuteWithAssetsPathUpdate()
    {
        // Mock the runProcessor method
        $processorResponse = $this->getMockBuilder('MODX\Revolution\Processors\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();

        $processorResponse->method('getResponse')
            ->willReturn(json_encode([
                'success' => true,
                'object' => ['id' => 789]
            ]));
        $processorResponse->method('isError')->willReturn(false);

        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->with(
                'Workspace\PackageNamespace\Update',
                $this->callback(function ($properties) {
                    return isset($properties['name']) && $properties['name'] === 'myns' &&
                           isset($properties['assets_path']) && $properties['assets_path'] === '/updated/assets/';
                }),
                $this->anything()
            )
            ->willReturn($processorResponse);

        // Execute the command with assets_path option
        $this->commandTester->execute([
            'name' => 'myns',
            '--assets_path' => '/updated/assets/'
        ]);

        $this->assertEquals(0, $this->commandTester->getStatusCode());
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
                'message' => 'Namespace not found'
            ]));
        $processorResponse->method('isError')->willReturn(true);

        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->willReturn($processorResponse);

        // Execute the command
        $this->commandTester->execute([
            'name' => 'nonexistent'
        ]);

        // Verify the output shows error
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Failed to update namespace', $output);
        $this->assertStringContainsString('Namespace not found', $output);
    }
}
