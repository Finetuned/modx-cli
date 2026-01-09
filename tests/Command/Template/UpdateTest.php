<?php namespace MODX\CLI\Tests\Command\Template;

use MODX\CLI\Command\Template\Update;
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
        $this->assertEquals('Element\Template\Update', $processor);
    }

    public function testConfigureHasCorrectName()
    {
        $this->assertEquals('template:update', $this->command->getName());
    }

    public function testConfigureHasCorrectDescription()
    {
        $this->assertEquals('Update a MODX template', $this->command->getDescription());
    }

    public function testExecuteWithSuccessfulResponse()
    {
        // Mock existing template object
        $existingTemplate = $this->getMockBuilder('stdClass')
            ->addMethods(['get'])
            ->getMock();
        $existingTemplate->method('get')->willReturnMap([
            ['templatename', 'ExistingTemplate'],
            ['description', 'Existing description'],
            ['category', 1],
            ['content', '<html><body>[[*content]]</body></html>']
        ]);
        
        // Mock getObject to return existing template
        $this->modx->expects($this->once())
            ->method('getObject')
            ->with(\MODX\Revolution\modTemplate::class, '123', $this->anything())
            ->willReturn($existingTemplate);
        
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
                'Element\Template\Update',
                $this->callback(function($properties) {
                    // Verify that existing data is pre-populated and new data overrides it
                    return isset($properties['id']) && $properties['id'] === '123' &&
                           isset($properties['templatename']) && $properties['templatename'] === 'ExistingTemplate' && // Pre-populated
                           isset($properties['description']) && $properties['description'] === 'Updated description' && // Overridden
                           isset($properties['category']) && $properties['category'] === 2 && // Overridden (converted to int)
                           isset($properties['content']) && $properties['content'] === '<html><body>[[*content]] - Updated</body></html>'; // Overridden
                }),
                $this->anything()
            )
            ->willReturn($processorResponse);
        
        // Execute the command - note we don't need to specify --templatename anymore
        $this->commandTester->execute([
            'id' => '123',
            '--description' => 'Updated description',
            '--category' => '2',
            '--content' => '<html><body>[[*content]] - Updated</body></html>'
        ]);
        
        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Template updated successfully', $output);
        $this->assertStringContainsString('Template ID: 123', $output);
    }

    public function testExecuteWithNonExistentTemplate()
    {
        // Mock getObject to return null (template doesn't exist)
        $this->modx->expects($this->once())
            ->method('getObject')
            ->with(\MODX\Revolution\modTemplate::class, '999', $this->anything())
            ->willReturn(null);
        
        // runProcessor should not be called since the template doesn't exist
        $this->modx->expects($this->never())
            ->method('runProcessor');
        
        // Execute the command
        $this->commandTester->execute([
            'id' => '999',
            '--description' => 'Updated description'
        ]);
        
        // Verify the output shows error message
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Template with ID 999 not found', $output);
    }

    public function testExecuteWithFailedResponse()
    {
        // Mock existing template object
        $existingTemplate = $this->getMockBuilder('stdClass')
            ->addMethods(['get'])
            ->getMock();
        $existingTemplate->method('get')->willReturnMap([
            ['templatename', 'ExistingTemplate'],
            ['description', 'Existing description'],
            ['category', 1],
            ['content', '<html><body>[[*content]]</body></html>']
        ]);
        
        // Mock getObject to return existing template
        $this->modx->expects($this->once())
            ->method('getObject')
            ->with(\MODX\Revolution\modTemplate::class, '123', $this->anything())
            ->willReturn($existingTemplate);
        
        // Mock the runProcessor method to return a failed response
        $processorResponse = $this->getMockBuilder('MODX\Revolution\Processors\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $processorResponse->method('getResponse')
            ->willReturn(json_encode([
                'success' => false,
                'message' => 'Error updating template'
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
        $this->assertStringContainsString('Failed to update template', $output);
        $this->assertStringContainsString('Error updating template', $output);
    }

    public function testExecuteWithLockStaticOptions()
    {
        // Mock existing template object
        $existingTemplate = $this->getMockBuilder('stdClass')
            ->addMethods(['get'])
            ->getMock();
        $existingTemplate->method('get')->willReturnMap([
            ['templatename', 'ExistingTemplate'],
            ['description', 'Existing description'],
            ['category', 1],
            ['content', '<html><body>[[*content]]</body></html>']
        ]);

        $this->modx->expects($this->once())
            ->method('getObject')
            ->with(\MODX\Revolution\modTemplate::class, '123', $this->anything())
            ->willReturn($existingTemplate);

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
                'Element\Template\Update',
                $this->callback(function($properties) {
                    return isset($properties['locked']) && $properties['locked'] === 1 &&
                           isset($properties['static']) && $properties['static'] === 0 &&
                           isset($properties['static_file']) && $properties['static_file'] === 'core/templates/test.tpl' &&
                           isset($properties['icon']) && $properties['icon'] === 'icon-template';
                }),
                $this->anything()
            )
            ->willReturn($processorResponse);

        $this->commandTester->execute([
            'id' => '123',
            '--locked' => 'true',
            '--static' => 'false',
            '--static_file' => 'core/templates/test.tpl',
            '--icon' => 'icon-template'
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Template updated successfully', $output);
    }
}
