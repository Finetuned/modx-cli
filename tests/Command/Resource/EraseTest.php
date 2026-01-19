<?php

namespace MODX\CLI\Tests\Command\Resource;

use MODX\CLI\Command\Resource\Erase;
use MODX\CLI\Tests\Configuration\BaseTest;
use Symfony\Component\Console\Tester\CommandTester;

class EraseTest extends BaseTest
{
    protected $command;
    protected $commandTester;
    protected $modx;

    protected function setUp(): void
    {
        // Create a mock MODX object
        $this->modx = $this->createMock('MODX\Revolution\modX');

        // Create the command
        $this->command = new Erase();
        $this->command->modx = $this->modx;

        // Create a command tester
        $this->commandTester = new CommandTester($this->command);
    }

    public function testConfigureHasCorrectProcessorPath()
    {
        $processor = $this->getProtectedProperty($this->command, 'processor');
        $this->assertEquals('Resource\Trash\Purge', $processor);
    }

    public function testConfigureHasCorrectName()
    {
        $this->assertEquals('resource:erase', $this->command->getName());
    }

    public function testConfigureHasCorrectDescription()
    {
        $this->assertEquals('Erase a MODX resource (permanently delete from trash)', $this->command->getDescription());
    }

    public function testExecuteWithSuccessfulErase()
    {
        // Mock existing resource object that is in trash
        $existingResource = $this->getMockBuilder('stdClass')
            ->addMethods(['get'])
            ->getMock();
        $existingResource->method('get')->willReturnMap([
            ['pagetitle', 'Test Page'],
            ['deleted', 1]  // Resource is in trash
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
                'count_success' => 1,
                'count_failures' => 0
            ]));
        $processorResponse->method('isError')->willReturn(false);

        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->with(
                'Resource\Trash\Purge',
                $this->callback(function ($properties) {
                    // Verify 'ids' parameter is passed (not 'id')
                    return isset($properties['ids']) && $properties['ids'] === '123';
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
        $this->assertStringContainsString('Resource erased successfully (permanently deleted)', $output);
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

    public function testExecuteWithResourceNotInTrash()
    {
        // Mock existing resource object that is NOT in trash
        $existingResource = $this->getMockBuilder('stdClass')
            ->addMethods(['get'])
            ->getMock();
        $existingResource->method('get')->willReturnMap([
            ['pagetitle', 'Test Page'],
            ['deleted', 0]  // Resource is NOT in trash
        ]);

        // Mock getObject to return existing resource
        $this->modx->expects($this->once())
            ->method('getObject')
            ->with(\MODX\Revolution\modResource::class, '123', $this->anything())
            ->willReturn($existingResource);

        // runProcessor should not be called since resource is not in trash
        $this->modx->expects($this->never())
            ->method('runProcessor');

        // Execute the command
        $this->commandTester->execute([
            'id' => '123',
            '--force' => true
        ]);

        // Verify the output shows error message
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('is not in the trash', $output);
        $this->assertStringContainsString("Use 'resource:delete' to move it to trash first", $output);
    }

    public function testExecuteWithFailedResponse()
    {
        // Mock existing resource object that is in trash
        $existingResource = $this->getMockBuilder('stdClass')
            ->addMethods(['get'])
            ->getMock();
        $existingResource->method('get')->willReturnMap([
            ['pagetitle', 'Test Page'],
            ['deleted', 1]  // Resource is in trash
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
                'message' => 'Error erasing resource'
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
        $this->assertStringContainsString('Failed to erase resource', $output);
        $this->assertStringContainsString('Error erasing resource', $output);
    }

    public function testExecuteWithForceOption()
    {
        // Mock existing resource object that is in trash
        $existingResource = $this->getMockBuilder('stdClass')
            ->addMethods(['get'])
            ->getMock();
        $existingResource->method('get')->willReturnMap([
            ['pagetitle', 'Test Page'],
            ['deleted', 1]  // Resource is in trash
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
                'count_success' => 1,
                'count_failures' => 0
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
        $this->assertStringContainsString('Resource erased successfully', $output);
    }

    public function testIdsParameterIsPassedCorrectly()
    {
        // Mock existing resource object that is in trash
        $existingResource = $this->getMockBuilder('stdClass')
            ->addMethods(['get'])
            ->getMock();
        $existingResource->method('get')->willReturnMap([
            ['pagetitle', 'Test Page'],
            ['deleted', 1]
        ]);

        $this->modx->expects($this->once())
            ->method('getObject')
            ->willReturn($existingResource);

        // Verify that 'ids' parameter is passed as a string
        $processorResponse = $this->getMockBuilder('MODX\Revolution\Processors\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $processorResponse->method('getResponse')
            ->willReturn(json_encode(['success' => true, 'count_success' => 1, 'count_failures' => 0]));
        $processorResponse->method('isError')->willReturn(false);

        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->with(
                'Resource\Trash\Purge',
                $this->callback(function ($properties) {
                    // Ensure 'ids' is a string, not an integer
                    return isset($properties['ids']) &&
                           $properties['ids'] === '456' &&
                           is_string($properties['ids']);
                }),
                $this->anything()
            )
            ->willReturn($processorResponse);

        $this->commandTester->execute([
            'id' => '456',
            '--force' => true
        ]);

        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }
}
