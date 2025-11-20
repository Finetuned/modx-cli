<?php namespace MODX\CLI\Tests\Command\TV;

use MODX\CLI\Command\TV\Update;
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
        $this->assertEquals('Element\Tv\Update', $processor);
    }

    public function testConfigureHasCorrectName()
    {
        $this->assertEquals('tv:update', $this->command->getName());
    }

    public function testConfigureHasCorrectDescription()
    {
        $this->assertEquals('Update a MODX template variable', $this->command->getDescription());
    }

    public function testExecuteWithSuccessfulResponse()
    {
        // Mock existing TV object
        $existingTV = $this->createMock('MODX\Revolution\modTemplateVar');
        $existingTV->method('get')->willReturnMap([
            ['name', 'ExistingTV'],
            ['caption', 'Existing Caption'],
            ['description', 'Existing description'],
            ['category', 1],
            ['type', 'text'],
            ['default_text', 'Default value']
        ]);
        
        // Mock getObject to return existing TV
        $this->modx->expects($this->once())
            ->method('getObject')
            ->with(\MODX\Revolution\modTemplateVar::class, '123', $this->anything())
            ->willReturn($existingTV);
        
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
                'Element\Tv\Update',
                $this->callback(function($properties) {
                    // Verify that new data is properly set and types are converted correctly
                    return isset($properties['id']) && $properties['id'] === '123' &&
                           isset($properties['caption']) && $properties['caption'] === 'Updated Caption' &&
                           isset($properties['description']) && $properties['description'] === 'Updated description' &&
                           isset($properties['category']) && $properties['category'] === 2 && // Converted to int
                           isset($properties['type']) && $properties['type'] === 'textarea' &&
                           isset($properties['default_text']) && $properties['default_text'] === 'Updated default value' &&
                           isset($properties['templates']) && is_array($properties['templates']) && 
                           $properties['templates'] === ['1', '2', '3', '4']; // Converted from comma-separated string to array
                }),
                $this->anything()
            )
            ->willReturn($processorResponse);
        
        // Execute the command - note we don't need to specify --name anymore
        $this->commandTester->execute([
            'id' => '123',
            '--caption' => 'Updated Caption',
            '--description' => 'Updated description',
            '--category' => '2',
            '--type' => 'textarea',
            '--default_text' => 'Updated default value',
            '--templates' => '1,2,3,4'
        ]);
        
        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Template variable updated successfully', $output);
        $this->assertStringContainsString('Template variable ID: 123', $output);
    }

    public function testExecuteWithNonExistentTV()
    {
        // Mock getObject to return null (TV doesn't exist)
        $this->modx->expects($this->once())
            ->method('getObject')
            ->with(\MODX\Revolution\modTemplateVar::class, '999', $this->anything())
            ->willReturn(null);
        
        // runProcessor should not be called since the TV doesn't exist
        $this->modx->expects($this->never())
            ->method('runProcessor');
        
        // Execute the command
        $this->commandTester->execute([
            'id' => '999',
            '--description' => 'Updated description'
        ]);
        
        // Verify the output shows error message
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Template Variable with ID 999 not found', $output);
    }

    public function testExecuteWithFailedResponse()
    {
        // Mock existing TV object
        $existingTV = $this->createMock('MODX\Revolution\modTemplateVar');
        $existingTV->method('get')->willReturnMap([
            ['name', 'ExistingTV'],
            ['caption', 'Existing Caption'],
            ['description', 'Existing description'],
            ['category', 1],
            ['type', 'text'],
            ['default_text', 'Default value']
        ]);
        
        // Mock getObject to return existing TV
        $this->modx->expects($this->once())
            ->method('getObject')
            ->with(\MODX\Revolution\modTemplateVar::class, '123', $this->anything())
            ->willReturn($existingTV);
        
        // Mock the runProcessor method to return a failed response
        $processorResponse = $this->getMockBuilder('MODX\Revolution\Processors\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $processorResponse->method('getResponse')
            ->willReturn(json_encode([
                'success' => false,
                'message' => 'Error updating template variable'
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
        $this->assertStringContainsString('Failed to update template variable', $output);
        $this->assertStringContainsString('Error updating template variable', $output);
    }
}
