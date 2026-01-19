<?php

namespace MODX\CLI\Tests\Command\Resource;

use MODX\CLI\Command\Resource\Delete;
use MODX\CLI\Tests\Configuration\BaseTest;
use Symfony\Component\Console\Tester\CommandTester;

class DeleteTest extends BaseTest
{
    protected $command;
    protected $commandTester;
    protected $modx;

    protected function setUp(): void
    {
        // Create a mock MODX object
        $this->modx = $this->createMock('MODX\Revolution\modX');

        // Create the command
        $this->command = new Delete();
        $this->command->modx = $this->modx;

        // Create a command tester
        $this->commandTester = new CommandTester($this->command);
    }

    public function testConfigureHasCorrectProcessorPath()
    {
        $processor = $this->getProtectedProperty($this->command, 'processor');
        $this->assertEquals('Resource\Delete', $processor);
    }

    public function testConfigureHasCorrectName()
    {
        $this->assertEquals('resource:delete', $this->command->getName());
    }

    public function testConfigureHasCorrectDescription()
    {
        $this->assertEquals('Delete a MODX resource (move to trash)', $this->command->getDescription());
    }

    public function testExecuteWithSuccessfulResponse()
    {
        // Mock existing resource object
        $existingResource = $this->getMockBuilder('stdClass')
            ->addMethods(['get'])
            ->getMock();
        $existingResource->method('get')->willReturnMap([
            ['pagetitle', 'Test Page'],
            ['deleted', 0]
        ]);

        // Mock getObject to return existing resource
        $this->modx->expects($this->once())
            ->method('getObject')
            ->with(\MODX\Revolution\modResource::class, '123', $this->anything())
            ->willReturn($existingResource);

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
                'Resource\Delete',
                $this->callback(function ($properties) {
                    return isset($properties['id']) && $properties['id'] === '123';
                }),
                $this->anything()
            )
            ->willReturn($processorResponse);

        // Execute the command with --force to skip confirmation
        $this->commandTester->execute([
            'id' => '123',
            '--force' => true
        ]);

        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Resource deleted successfully (moved to trash)', $output);
    }

    public function testExecuteWithNonExistentResource()
    {
        // Mock getObject to return null (resource doesn't exist)
        $this->modx->expects($this->once())
            ->method('getObject')
            ->with(\MODX\Revolution\modResource::class, '999', $this->anything())
            ->willReturn(null);

        // runProcessor should not be called since the resource doesn't exist
        $this->modx->expects($this->never())
            ->method('runProcessor');

        // Execute the command
        $this->commandTester->execute([
            'id' => '999',
            '--force' => true
        ]);

        // Verify the output shows error message
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Resource with ID 999 not found', $output);
    }

    public function testExecuteWithFailedResponse()
    {
        // Mock existing resource object
        $existingResource = $this->getMockBuilder('stdClass')
            ->addMethods(['get'])
            ->getMock();
        $existingResource->method('get')->willReturnMap([
            ['pagetitle', 'Test Page'],
            ['deleted', 0]
        ]);

        // Mock getObject to return existing resource
        $this->modx->expects($this->once())
            ->method('getObject')
            ->with(\MODX\Revolution\modResource::class, '123', $this->anything())
            ->willReturn($existingResource);

        // Mock the runProcessor method to return a failed response
        $processorResponse = $this->getMockBuilder('MODX\Revolution\Processors\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $processorResponse->method('getResponse')
            ->willReturn(json_encode([
                'success' => false,
                'message' => 'Error deleting resource'
            ]));
        $processorResponse->method('isError')->willReturn(true);

        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->willReturn($processorResponse);

        // Execute the command
        $this->commandTester->execute([
            'id' => '123',
            '--force' => true
        ]);

        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Failed to delete resource', $output);
        $this->assertStringContainsString('Error deleting resource', $output);
    }

    public function testExecuteWithForceOption()
    {
        // Mock existing resource object
        $existingResource = $this->getMockBuilder('stdClass')
            ->addMethods(['get'])
            ->getMock();
        $existingResource->method('get')->willReturnMap([
            ['pagetitle', 'Test Page'],
            ['deleted', 0]
        ]);

        // Mock getObject to return existing resource
        $this->modx->expects($this->once())
            ->method('getObject')
            ->with(\MODX\Revolution\modResource::class, '123', $this->anything())
            ->willReturn($existingResource);

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
            ->willReturn($processorResponse);

        // Execute the command with --force flag
        $this->commandTester->execute([
            'id' => '123',
            '--force' => true
        ]);

        // Verify the command executed without asking for confirmation
        $output = $this->commandTester->getDisplay();
        $this->assertStringNotContainsString('Are you sure', $output);
        $this->assertStringContainsString('Resource deleted successfully', $output);
    }
}
