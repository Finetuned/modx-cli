<?php namespace MODX\CLI\Tests\Command\Template;

use MODX\CLI\Command\Template\Remove;
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
        $application = new Application();
        $application->add($this->command);
        $this->commandTester = new CommandTester($this->command);
    }

    public function testConfigureHasCorrectProcessorPath()
    {
        $processor = $this->getProtectedProperty($this->command, 'processor');
        $this->assertEquals('Element\Template\Remove', $processor);
    }

    public function testConfigureHasCorrectName()
    {
        $this->assertEquals('template:remove', $this->command->getName());
    }

    public function testConfigureHasCorrectDescription()
    {
        $this->assertEquals('Remove a MODX template', $this->command->getDescription());
    }

    public function testExecuteWithSuccessfulResponse()
    {
        // Mock the template object
        $template = $this->getMockBuilder('MODX\Revolution\modTemplate')
            ->disableOriginalConstructor()
            ->getMock();
        $template->method('get')
            ->with('templatename')
            ->willReturn('Test Template');
        
        // Mock the getObject method
        $this->modx->method('getObject')
            ->with('modTemplate', '123')
            ->willReturn($template);
        
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
                'Element\Template\Remove',
                $this->callback(function($properties) {
                    return isset($properties['id']) && $properties['id'] === '123';
                }),
                $this->anything()
            )
            ->willReturn($processorResponse);
        
        // Execute the command with force option
        $this->commandTester->execute([
            'command' => 'template:remove',
            'id' => '123',
            '--force' => true
        ]);
        
        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Template removed successfully', $output);
    }

    public function testExecuteWithTemplateNotFound()
    {
        // Mock the getObject method to return null (template not found)
        $this->modx->method('getObject')
            ->with('modTemplate', '999')
            ->willReturn(null);
        
        // Execute the command
        $this->commandTester->execute([
            'command' => 'template:remove',
            'id' => '999'
        ]);
        
        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Template with ID 999 not found', $output);
    }

    public function testExecuteWithFailedResponse()
    {
        // Mock the template object
        $template = $this->getMockBuilder('MODX\Revolution\modTemplate')
            ->disableOriginalConstructor()
            ->getMock();
        $template->method('get')
            ->with('templatename')
            ->willReturn('Test Template');
        
        // Mock the getObject method
        $this->modx->method('getObject')
            ->with('modTemplate', '123')
            ->willReturn($template);
        
        // Mock the runProcessor method to return a failed response
        $processorResponse = $this->getMockBuilder('MODX\Revolution\Processors\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $processorResponse->method('getResponse')
            ->willReturn(json_encode([
                'success' => false,
                'message' => 'Error removing template'
            ]));
        $processorResponse->method('isError')->willReturn(true);
        
        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->willReturn($processorResponse);
        
        // Execute the command with force option
        $this->commandTester->execute([
            'command' => 'template:remove',
            'id' => '123',
            '--force' => true
        ]);
        
        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Failed to remove template', $output);
        $this->assertStringContainsString('Error removing template', $output);
    }
}
