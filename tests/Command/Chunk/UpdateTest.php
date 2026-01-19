<?php

namespace MODX\CLI\Tests\Command\Chunk;

use MODX\CLI\Command\Chunk\Update;
//use PHPUnit\Framework\TestCase;
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
        $this->assertEquals('Element\Chunk\Update', $processor);
    }

    public function testConfigureHasCorrectName()
    {
        $this->assertEquals('chunk:update', $this->command->getName());
    }

    public function testConfigureHasCorrectDescription()
    {
        $this->assertEquals('Update a MODX chunk', $this->command->getDescription());
    }

    public function testExecuteWithSuccessfulResponse()
    {
        // Mock existing chunk object
        $existingChunk = $this->getMockBuilder('stdClass')
            ->addMethods(['get'])
            ->getMock();
        $existingChunk->method('get')->willReturnMap([
            ['name', 'ExistingChunk'],
            ['description', 'Existing description'],
            ['category', 1],
            ['snippet', '<p>Existing content</p>']
        ]);

        // Mock getObject to return existing chunk
        $this->modx->expects($this->once())
            ->method('getObject')
            ->with(\MODX\Revolution\modChunk::class, '123', $this->anything())
            ->willReturn($existingChunk);

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
                'Element\Chunk\Update',
                $this->callback(function ($properties) {
                    // Verify that existing data is pre-populated and new data overrides it
                    return isset($properties['id']) && $properties['id'] === '123' &&
                           isset($properties['name']) && $properties['name'] === 'ExistingChunk' && // Pre-populated
                           isset($properties['description']) && $properties['description'] === 'Updated description' && // Overridden
                           isset($properties['category']) && $properties['category'] === 2 && // Overridden (converted to int)
                           isset($properties['snippet']) && $properties['snippet'] === '<p>Updated content</p>'; // Overridden
                }),
                $this->anything()
            )
            ->willReturn($processorResponse);

        // Execute the command - note we don't need to specify --name anymore
        $this->commandTester->execute([
            'id' => '123',
            '--description' => 'Updated description',
            '--category' => '2',
            '--snippet' => '<p>Updated content</p>'
        ]);

        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Chunk updated successfully', $output);
        $this->assertStringContainsString('Chunk ID: 123', $output);
    }

    public function testExecuteWithNonExistentChunk()
    {
        // Mock getObject to return null (chunk doesn't exist)
        $this->modx->expects($this->once())
            ->method('getObject')
            ->with(\MODX\Revolution\modChunk::class, '999', $this->anything())
            ->willReturn(null);

        // runProcessor should not be called since the chunk doesn't exist
        $this->modx->expects($this->never())
            ->method('runProcessor');

        // Execute the command
        $this->commandTester->execute([
            'id' => '999',
            '--description' => 'Updated description'
        ]);

        // Verify the output shows error message
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Chunk with ID 999 not found', $output);
    }

    public function testExecuteWithFailedResponse()
    {
        // Mock existing chunk object
        $existingChunk = $this->getMockBuilder('stdClass')
            ->addMethods(['get'])
            ->getMock();
        $existingChunk->method('get')->willReturnMap([
            ['name', 'ExistingChunk'],
            ['description', 'Existing description'],
            ['category', 1],
            ['snippet', '<p>Existing content</p>']
        ]);

        // Mock getObject to return existing chunk
        $this->modx->expects($this->once())
            ->method('getObject')
            ->with(\MODX\Revolution\modChunk::class, '123', $this->anything())
            ->willReturn($existingChunk);

        // Mock the runProcessor method to return a failed response
        $processorResponse = $this->getMockBuilder('MODX\Revolution\Processors\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $processorResponse->method('getResponse')
            ->willReturn(json_encode([
                'success' => false,
                'message' => 'Error updating chunk'
            ]));
        $processorResponse->method('isError')->willReturn(true);

        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->willReturn($processorResponse);

        // Execute the command
        $this->commandTester->execute([
            'id' => '123',
            '--description' => 'Updated description'
        ]);

        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Failed to update chunk', $output);
        $this->assertStringContainsString('Error updating chunk', $output);
    }

    public function testExecuteWithLockStaticOptions()
    {
        // Mock existing chunk object
        $existingChunk = $this->getMockBuilder('stdClass')
            ->addMethods(['get'])
            ->getMock();
        $existingChunk->method('get')->willReturnMap([
            ['name', 'ExistingChunk'],
            ['description', 'Existing description'],
            ['category', 1],
            ['snippet', '<p>Existing content</p>']
        ]);

        $this->modx->expects($this->once())
            ->method('getObject')
            ->with(\MODX\Revolution\modChunk::class, '123', $this->anything())
            ->willReturn($existingChunk);

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
                'Element\Chunk\Update',
                $this->callback(function ($properties) {
                    return isset($properties['locked']) && $properties['locked'] === 1 &&
                           isset($properties['static']) && $properties['static'] === 0 &&
                           isset($properties['static_file']) && $properties['static_file'] === 'core/chunks/test.tpl';
                }),
                $this->anything()
            )
            ->willReturn($processorResponse);

        $this->commandTester->execute([
            'id' => '123',
            '--locked' => 'true',
            '--static' => 'false',
            '--static_file' => 'core/chunks/test.tpl'
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Chunk updated successfully', $output);
    }
}
