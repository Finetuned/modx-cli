<?php namespace MODX\CLI\Tests\Command\Snippet;

use MODX\CLI\Command\Snippet\Update;
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
        $this->assertEquals('element/snippet/update', $processor);
    }

    public function testConfigureHasCorrectName()
    {
        $this->assertEquals('snippet:update', $this->command->getName());
    }

    public function testConfigureHasCorrectDescription()
    {
        $this->assertEquals('Update a MODX snippet', $this->command->getDescription());
    }

    public function testExecuteWithSuccessfulResponse()
    {
        // Mock existing snippet object
        $existingSnippet = $this->getMockBuilder('stdClass')
            ->addMethods(['get'])
            ->getMock();
        $existingSnippet->method('get')->willReturnMap([
            ['name', 'ExistingSnippet'],
            ['description', 'Existing description'],
            ['category', 1],
            ['snippet', '<?php return "Hello World!";']
        ]);
        
        // Mock getObject to return existing snippet
        $this->modx->expects($this->once())
            ->method('getObject')
            ->with('modSnippet', '123')
            ->willReturn($existingSnippet);
        
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
                'element/snippet/update',
                $this->callback(function($properties) {
                    // Verify that existing data is pre-populated and new data overrides it
                    return isset($properties['id']) && $properties['id'] === '123' &&
                           isset($properties['name']) && $properties['name'] === 'ExistingSnippet' && // Pre-populated
                           isset($properties['description']) && $properties['description'] === 'Updated description' && // Overridden
                           isset($properties['category']) && $properties['category'] === 2 && // Overridden (converted to int)
                           isset($properties['snippet']) && $properties['snippet'] === '<?php return "Updated Hello World!";'; // Overridden
                }),
                $this->anything()
            )
            ->willReturn($processorResponse);
        
        // Execute the command - note we don't need to specify --name anymore
        $this->commandTester->execute([
            'id' => '123',
            '--description' => 'Updated description',
            '--category' => '2',
            '--snippet' => '<?php return "Updated Hello World!";'
        ]);
        
        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Snippet updated successfully', $output);
        $this->assertStringContainsString('Snippet ID: 123', $output);
    }

    public function testExecuteWithNonExistentSnippet()
    {
        // Mock getObject to return null (snippet doesn't exist)
        $this->modx->expects($this->once())
            ->method('getObject')
            ->with('modSnippet', '999')
            ->willReturn(null);
        
        // runProcessor should not be called since the snippet doesn't exist
        $this->modx->expects($this->never())
            ->method('runProcessor');
        
        // Execute the command
        $this->commandTester->execute([
            'id' => '999',
            '--description' => 'Updated description'
        ]);
        
        // Verify the output shows error message
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Snippet with ID 999 not found', $output);
    }

    public function testExecuteWithFailedResponse()
    {
        // Mock existing snippet object
        $existingSnippet = $this->getMockBuilder('stdClass')
            ->addMethods(['get'])
            ->getMock();
        $existingSnippet->method('get')->willReturnMap([
            ['name', 'ExistingSnippet'],
            ['description', 'Existing description'],
            ['category', 1],
            ['snippet', '<?php return "Hello World!";']
        ]);
        
        // Mock getObject to return existing snippet
        $this->modx->expects($this->once())
            ->method('getObject')
            ->with('modSnippet', '123')
            ->willReturn($existingSnippet);
        
        // Mock the runProcessor method to return a failed response
        $processorResponse = $this->getMockBuilder('MODX\Revolution\Processors\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $processorResponse->method('getResponse')
            ->willReturn(json_encode([
                'success' => false,
                'message' => 'Error updating snippet'
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
        $this->assertStringContainsString('Failed to update snippet', $output);
        $this->assertStringContainsString('Error updating snippet', $output);
    }
}
