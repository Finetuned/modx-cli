<?php namespace MODX\CLI\Tests\Command\TV;

use MODX\CLI\Command\TV\Remove;
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
        $this->assertEquals('Element\Tv\Remove', $processor);
    }

    public function testConfigureHasCorrectName()
    {
        $this->assertEquals('tv:remove', $this->command->getName());
    }

    public function testConfigureHasCorrectDescription()
    {
        $this->assertEquals('Remove a MODX template variable', $this->command->getDescription());
    }

    public function testExecuteWithSuccessfulResponse()
    {
        // Mock the TV object
        $tv = $this->getMockBuilder('MODX\Revolution\modTemplateVar')
            ->disableOriginalConstructor()
            ->getMock();
        $tv->method('get')
            ->with('name')
            ->willReturn('Test TV');
        
        // Mock the getObject method
        $this->modx->method('getObject')
            ->with('modTemplateVar', '123')
            ->willReturn($tv);
        
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
                'Element\Tv\Remove',
                $this->callback(function($properties) {
                    return isset($properties['id']) && $properties['id'] === '123';
                }),
                $this->anything()
            )
            ->willReturn($processorResponse);
        
        // Execute the command with force option
        $this->commandTester->execute([
            'command' => 'tv:remove',
            'id' => '123',
            '--force' => true
        ]);
        
        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Template variable removed successfully', $output);
    }

    public function testExecuteWithTVNotFound()
    {
        // Mock the getObject method to return null (TV not found)
        $this->modx->method('getObject')
            ->with('modTemplateVar', '999')
            ->willReturn(null);
        
        // Execute the command
        $this->commandTester->execute([
            'command' => 'tv:remove',
            'id' => '999'
        ]);
        
        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Template variable with ID 999 not found', $output);
    }

    public function testExecuteWithFailedResponse()
    {
        // Mock the TV object
        $tv = $this->getMockBuilder('MODX\Revolution\modTemplateVar')
            ->disableOriginalConstructor()
            ->getMock();
        $tv->method('get')
            ->with('name')
            ->willReturn('Test TV');
        
        // Mock the getObject method
        $this->modx->method('getObject')
            ->with('modTemplateVar', '123')
            ->willReturn($tv);
        
        // Mock the runProcessor method to return a failed response
        $processorResponse = $this->getMockBuilder('MODX\Revolution\Processors\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $processorResponse->method('getResponse')
            ->willReturn(json_encode([
                'success' => false,
                'message' => 'Error removing template variable'
            ]));
        $processorResponse->method('isError')->willReturn(true);
        
        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->willReturn($processorResponse);
        
        // Execute the command with force option
        $this->commandTester->execute([
            'command' => 'tv:remove',
            'id' => '123',
            '--force' => true
        ]);
        
        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Failed to remove template variable', $output);
        $this->assertStringContainsString('Error removing template variable', $output);
    }
}
