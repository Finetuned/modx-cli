<?php namespace MODX\CLI\Tests\Command\Ns;

use MODX\CLI\Command\Ns\Create;
use MODX\CLI\Tests\Configuration\BaseTest;
use Symfony\Component\Console\Tester\CommandTester;

class CreateTest extends BaseTest
{
    protected $command;
    protected $commandTester;
    protected $modx;

    protected function setUp(): void
    {
        // Create a mock MODX object
        $this->modx = $this->createMock('MODX\Revolution\modX');
        
        // Create the command
        $this->command = new Create();
        $this->command->modx = $this->modx;
        
        // Create a command tester
        $this->commandTester = new CommandTester($this->command);
    }

    public function testConfigureHasCorrectProcessorPath()
    {
        $processor = $this->getProtectedProperty($this->command, 'processor');
        $this->assertEquals('Workspace\PackageNamespace\Create', $processor);
    }

    public function testConfigureHasCorrectName()
    {
        $this->assertEquals('ns:create', $this->command->getName());
    }

    public function testConfigureHasCorrectDescription()
    {
        $this->assertEquals('Create a namespace in MODX', $this->command->getDescription());
    }

    public function testExecuteWithSuccessfulCreate()
    {
        // Mock the runProcessor method to return a successful response
        $processorResponse = $this->getMockBuilder('MODX\Revolution\Processors\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        
        $processorResponse->method('getResponse')
            ->willReturn(json_encode([
                'success' => true,
                'object' => ['id' => 123, 'name' => 'testnamespace']
            ]));
        $processorResponse->method('isError')->willReturn(false);
        
        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->with(
                'Workspace\PackageNamespace\Create',
                $this->callback(function($properties) {
                    return isset($properties['name']) && $properties['name'] === 'testnamespace';
                }),
                $this->anything()
            )
            ->willReturn($processorResponse);
        
        // Execute the command
        $this->commandTester->execute([
            'name' => 'testnamespace'
        ]);
        
        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Namespace created successfully', $output);
        $this->assertStringContainsString('Namespace ID: 123', $output);
    }

    public function testExecuteWithPathAndAssetsPath()
    {
        // Mock the runProcessor method
        $processorResponse = $this->getMockBuilder('MODX\Revolution\Processors\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        
        $processorResponse->method('getResponse')
            ->willReturn(json_encode([
                'success' => true,
                'object' => ['id' => 456]
            ]));
        $processorResponse->method('isError')->willReturn(false);
        
        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->with(
                'Workspace\PackageNamespace\Create',
                $this->callback(function($properties) {
                    return isset($properties['name']) && $properties['name'] === 'myns' &&
                           isset($properties['path']) && $properties['path'] === '/custom/path/' &&
                           isset($properties['assets_path']) && $properties['assets_path'] === '/custom/assets/';
                }),
                $this->anything()
            )
            ->willReturn($processorResponse);
        
        // Execute the command with path and assets_path options
        $this->commandTester->execute([
            'name' => 'myns',
            '--path' => '/custom/path/',
            '--assets_path' => '/custom/assets/'
        ]);
        
        $this->assertEquals(0, $this->commandTester->getStatusCode());
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
                'message' => 'Namespace already exists'
            ]));
        $processorResponse->method('isError')->willReturn(true);
        
        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->willReturn($processorResponse);
        
        // Execute the command
        $this->commandTester->execute([
            'name' => 'duplicate'
        ]);
        
        // Verify the output shows error
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Failed to create namespace', $output);
        $this->assertStringContainsString('Namespace already exists', $output);
    }
}
