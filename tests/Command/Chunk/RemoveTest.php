<?php namespace MODX\CLI\Tests\Command\Chunk;

use MODX\CLI\Command\Chunk\Remove;
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
        $this->assertEquals('Element\Chunk\Remove', $processor);
    }

    public function testConfigureHasCorrectName()
    {
        $this->assertEquals('chunk:remove', $this->command->getName());
    }

    public function testConfigureHasCorrectDescription()
    {
        $this->assertEquals('Remove a MODX chunk', $this->command->getDescription());
    }

    public function testExecuteWithSuccessfulResponse()
    {
        // Mock the chunk object
        $chunk = $this->getMockBuilder('MODX\Revolution\modChunk')
            ->disableOriginalConstructor()
            ->getMock();
        $chunk->method('get')
            ->with('name')
            ->willReturn('Test Chunk');
        
        // Mock the getObject method
        $this->modx->method('getObject')
            ->with(\MODX\Revolution\modChunk::class, '123', $this->anything())
            ->willReturn($chunk);
        
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
                'Element\Chunk\Remove',
                $this->callback(function($properties) {
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
        $this->assertStringContainsString('Chunk removed successfully', $output);
    }

    public function testExecuteWithChunkNotFound()
    {
        // Mock the getObject method to return null (chunk not found)
        $this->modx->method('getObject')
            ->with(\MODX\Revolution\modChunk::class, '999', $this->anything())
            ->willReturn(null);
        
        // Execute the command
        $this->commandTester->execute([
            'id' => '999'
        ]);
        
        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Chunk with ID 999 not found', $output);
    }

    public function testExecuteWithFailedResponse()
    {
        // Mock the chunk object
        $chunk = $this->getMockBuilder('MODX\Revolution\modChunk')
            ->disableOriginalConstructor()
            ->getMock();
        $chunk->method('get')
            ->with('name')
            ->willReturn('Test Chunk');
        
        // Mock the getObject method
        $this->modx->method('getObject')
            ->with(\MODX\Revolution\modChunk::class, '123', $this->anything())
            ->willReturn($chunk);
        
        // Mock the runProcessor method to return a failed response
        $processorResponse = $this->getMockBuilder('MODX\Revolution\Processors\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $processorResponse->method('getResponse')
            ->willReturn(json_encode([
                'success' => false,
                'message' => 'Error removing chunk'
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
        $this->assertStringContainsString('Failed to remove chunk', $output);
        $this->assertStringContainsString('Error removing chunk', $output);
    }
}
