<?php

namespace MODX\CLI\Tests\Command\Resource;

use MODX\CLI\Command\Resource\Update;
use MODX\CLI\Tests\Configuration\BaseTest;
use MODX\CLI\Application;
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

        // Create a command tester without using the Application class to avoid conflicts
        $this->commandTester = new CommandTester($this->command);
    }

    public function testConfigureHasCorrectProcessorPath()
    {
        $processor = $this->getProtectedProperty($this->command, 'processor');
        $this->assertEquals('Resource\Update', $processor);
    }

    public function testConfigureHasCorrectName()
    {
        $this->assertEquals('resource:update', $this->command->getName());
    }

    public function testConfigureHasCorrectDescription()
    {
        $this->assertEquals('Update a MODX resource', $this->command->getDescription());
    }

    public function testExecuteWithSuccessfulResponse()
    {
        // Mock existing resource object with all essential fields
        $existingResource = $this->getMockBuilder('stdClass')
            ->addMethods(['get'])
            ->getMock();
        $existingResource->method('get')->willReturnMap([
            ['pagetitle', 'Existing Page'],
            ['parent', 0],
            ['template', 1],
            ['published', 1],
            ['class_key', 'modDocument'],
            ['context_key', 'web'],
            ['content_type', 1],
            ['alias', 'existing-page'],
            ['content', 'Existing content'],
            ['hidemenu', 0],
            ['searchable', 1],
            ['cacheable', 1]
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
                'Resource\Update',
                $this->callback(function ($properties) {
                    // Verify that existing data is pre-populated and critical fields are set
                    return isset($properties['id']) && $properties['id'] === '123' &&
                           isset($properties['pagetitle']) && $properties['pagetitle'] === 'Updated Title' && // Overridden
                           isset($properties['class_key']) && $properties['class_key'] === 'modDocument' && // Pre-populated
                           isset($properties['context_key']) && $properties['context_key'] === 'web' && // Pre-populated
                           isset($properties['content_type']) && $properties['content_type'] === 1 && // Pre-populated
                           isset($properties['alias']) && $properties['alias'] === 'updated-alias'; // Overridden
                }),
                $this->anything()
            )
            ->willReturn($processorResponse);

        // Execute the command
        $this->commandTester->execute([
            'id' => '123',
            '--pagetitle' => 'Updated Title',
            '--alias' => 'updated-alias'
        ]);

        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Resource updated successfully', $output);
        $this->assertStringContainsString('Resource ID: 123', $output);
    }

    public function testExecuteWithCriticalFieldDefaults()
    {
        // Mock existing resource object with missing critical fields
        $existingResource = $this->getMockBuilder('stdClass')
            ->addMethods(['get'])
            ->getMock();
        $existingResource->method('get')->willReturnMap([
            ['pagetitle', 'Existing Page'],
            ['parent', 0],
            ['template', 1],
            ['published', 1],
            ['class_key', null], // Missing - should get default
            ['context_key', ''], // Empty - should get default
            ['content_type', null], // Missing - should get default
            ['alias', 'existing-page'],
            ['content', 'Existing content'],
            ['hidemenu', 0],
            ['searchable', 1],
            ['cacheable', 1]
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
                'Resource\Update',
                $this->callback(function ($properties) {
                    // Verify that critical fields get proper defaults
                    return isset($properties['class_key']) && $properties['class_key'] === 'modDocument' &&
                           isset($properties['context_key']) && $properties['context_key'] === 'web' &&
                           isset($properties['content_type']) && $properties['content_type'] === 1;
                }),
                $this->anything()
            )
            ->willReturn($processorResponse);

        // Execute the command
        $this->commandTester->execute([
            'id' => '123',
            '--pagetitle' => 'Updated Title'
        ]);

        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Resource updated successfully', $output);
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
            '--pagetitle' => 'Updated Title'
        ]);

        // Verify the output shows error message
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Resource with ID 999 not found', $output);
    }

    public function testExecuteWithBooleanFields()
    {
        // Mock existing resource object
        $existingResource = $this->getMockBuilder('stdClass')
            ->addMethods(['get'])
            ->getMock();
        $existingResource->method('get')->willReturnMap([
            ['pagetitle', 'Existing Page'],
            ['parent', 0],
            ['template', 1],
            ['published', 0], // Will be overridden
            ['class_key', 'modDocument'],
            ['context_key', 'web'],
            ['content_type', 1],
            ['alias', 'existing-page'],
            ['content', 'Existing content'],
            ['hidemenu', 1], // Will be overridden
            ['searchable', 1],
            ['cacheable', 1]
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
                'Resource\Update',
                $this->callback(function ($properties) {
                    // Verify boolean fields are properly converted to integers
                    return isset($properties['published']) && $properties['published'] === 1 &&
                           isset($properties['hidemenu']) && $properties['hidemenu'] === 0;
                }),
                $this->anything()
            )
            ->willReturn($processorResponse);

        // Execute the command with boolean values
        $this->commandTester->execute([
            'id' => '123',
            '--published' => 'true',
            '--hidemenu' => 'false'
        ]);

        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Resource updated successfully', $output);
    }

    public function testExecuteWithAdditionalOptions()
    {
        // Mock existing resource object
        $existingResource = $this->getMockBuilder('stdClass')
            ->addMethods(['get'])
            ->getMock();
        $existingResource->method('get')->willReturnMap([
            ['pagetitle', 'Existing Page'],
            ['parent', 0],
            ['template', 1],
            ['published', 1],
            ['class_key', 'modDocument'],
            ['context_key', 'web'],
            ['content_type', 1],
            ['alias', 'existing-page'],
            ['content', 'Existing content'],
            ['hidemenu', 0],
            ['searchable', 1],
            ['cacheable', 1]
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
                'Resource\Update',
                $this->callback(function ($properties) {
                    return isset($properties['parent']) && $properties['parent'] === 10 &&
                           isset($properties['template']) && $properties['template'] === 2 &&
                           isset($properties['content']) && $properties['content'] === 'Updated content' &&
                           isset($properties['context_key']) && $properties['context_key'] === 'web';
                }),
                $this->anything()
            )
            ->willReturn($processorResponse);

        // Execute the command with additional options
        $this->commandTester->execute([
            'id' => '123',
            '--parent' => '10',
            '--template' => '2',
            '--content' => 'Updated content',
            '--context_key' => 'web'
        ]);

        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Resource updated successfully', $output);
    }

    public function testExecuteWithFailedResponse()
    {
        // Mock existing resource object
        $existingResource = $this->getMockBuilder('stdClass')
            ->addMethods(['get'])
            ->getMock();
        $existingResource->method('get')->willReturnMap([
            ['pagetitle', 'Existing Page'],
            ['parent', 0],
            ['template', 1],
            ['published', 1],
            ['class_key', 'modDocument'],
            ['context_key', 'web'],
            ['content_type', 1],
            ['alias', 'existing-page'],
            ['content', 'Existing content'],
            ['hidemenu', 0],
            ['searchable', 1],
            ['cacheable', 1]
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
                'message' => 'Error updating resource'
            ]));
        $processorResponse->method('isError')->willReturn(true);

        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->willReturn($processorResponse);

        // Execute the command
        $this->commandTester->execute([
            'id' => '123',
            '--pagetitle' => 'Updated Title'
        ]);

        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Failed to update resource', $output);
        $this->assertStringContainsString('Error updating resource', $output);
    }

    public function testExecuteWithMinimalPagetitleUpdate()
    {
        // Mock existing resource object with all essential fields
        $existingResource = $this->getMockBuilder('stdClass')
            ->addMethods(['get'])
            ->getMock();
        $existingResource->method('get')->willReturnMap([
            ['pagetitle', 'Old Title'],
            ['parent', 0],
            ['template', 1],
            ['published', 1],
            ['class_key', 'modDocument'],
            ['context_key', 'web'],
            ['content_type', 1],
            ['alias', 'old-alias'],
            ['content', 'Existing content'],
            ['hidemenu', 0],
            ['searchable', 1],
            ['cacheable', 1]
        ]);

        // Mock getObject to return existing resource
        $this->modx->expects($this->once())
            ->method('getObject')
            ->with(\MODX\Revolution\modResource::class, '2', $this->anything())
            ->willReturn($existingResource);

        // Mock the runProcessor method to return a successful response
        $processorResponse = $this->getMockBuilder('MODX\Revolution\Processors\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $processorResponse->method('getResponse')
            ->willReturn(json_encode([
                'success' => true,
                'object' => ['id' => 2]
            ]));
        $processorResponse->method('isError')->willReturn(false);

        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->with(
                'Resource\Update',
                $this->callback(function ($properties) {
                    // Verify that only pagetitle is updated, other fields are pre-populated
                    return isset($properties['id']) && $properties['id'] === '2' &&
                           isset($properties['pagetitle']) && $properties['pagetitle'] === 'Talks' &&
                           isset($properties['class_key']) && $properties['class_key'] === 'modDocument' &&
                           isset($properties['context_key']) && $properties['context_key'] === 'web';
                }),
                $this->anything()
            )
            ->willReturn($processorResponse);

        // Execute the command with minimal syntax (regression test for issue in progress.md)
        $this->commandTester->execute([
            'id' => '2',
            '--pagetitle' => 'Talks'
        ]);

        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Resource updated successfully', $output);
        $this->assertStringContainsString('Resource ID: 2', $output);
    }
}
