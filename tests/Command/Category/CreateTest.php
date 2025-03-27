<?php namespace MODX\CLI\Tests\Command\Category;


use MODX\CLI\Command\Category\Create;
//use PHPUnit\Framework\TestCase;
use MODX\CLI\Tests\Configuration\BaseTest;
use MODX\CLI\Application;
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
        $application = new Application();
        $application->add($this->command);
        $this->commandTester = new CommandTester($this->command);
    }

    public function testConfigureHasCorrectProcessorPath()
    {
        $processor = $this->getProtectedProperty($this->command, 'processor');

        $this->assertEquals('element/category/create', $processor);
    }

    public function testConfigureHasCorrectName()
    {
        $this->assertEquals('category:create', $this->command->getName());
    }

    public function testConfigureHasCorrectDescription()
    {
        $this->assertEquals('Create a MODX category', $this->command->getDescription());
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
                'element/category/create',
                $this->callback(function($properties) {
                    return isset($properties['category']) && $properties['category'] === 'TestCategory';
                }),
                $this->anything()
            )
            ->willReturn($processorResponse);
        
        // Execute the command
        $this->commandTester->execute([
            'command' => $this->command->getName(),
            'category' => 'TestCategory',
            '--parent' => '1',
            '--rank' => '5'
        ]);
        
        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Category created successfully', $output);
        $this->assertStringContainsString('Category ID: 123', $output);
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
                'message' => 'Error creating category'
            ]));
        $processorResponse->method('isError')->willReturn(true);
        
        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->willReturn($processorResponse);
        
        // Execute the command
        $this->commandTester->execute([
            'command' => $this->command->getName(),
            'category' => 'TestCategory'
        ]);
        
        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Failed to create category', $output);
        $this->assertStringContainsString('Error creating category', $output);
    }
}
