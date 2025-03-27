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
        
        // Create a command tester
        $application = new Application();
        $application->add($this->command);
        $this->commandTester = new CommandTester($this->command);
    }

    public function testConfigureHasCorrectProcessorPath()
    {
        $processor = $this->getProtectedProperty($this->command, 'processor');
        $this->assertEquals('element/tv/update', $processor);
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
                'element/tv/update',
                $this->callback(function($properties) {
                    return isset($properties['id']) && $properties['id'] === '123' &&
                           isset($properties['name']) && $properties['name'] === 'UpdatedTV';
                }),
                $this->anything()
            )
            ->willReturn($processorResponse);
        
        // Execute the command
        $this->commandTester->execute([
            'command' => 'tv:update',
            'id' => '123',
            '--name' => 'UpdatedTV',
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

    public function testExecuteWithFailedResponse()
    {
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
            'command' => 'tv:update',
            'id' => '123',
            '--name' => 'UpdatedTV'
        ]);
        
        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Failed to update template variable', $output);
        $this->assertStringContainsString('Error updating template variable', $output);
    }
}
