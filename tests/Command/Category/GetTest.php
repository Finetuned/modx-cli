<?php namespace MODX\CLI\Tests\Command\Category;

use MODX\CLI\Command\Category\Get;
//use PHPUnit\Framework\TestCase;
use MODX\CLI\Tests\Configuration\BaseTest;
use MODX\CLI\Application;
use Symfony\Component\Console\Tester\CommandTester;

class GetTest extends BaseTest
{
    protected $command;
    protected $commandTester;
    protected $modx;

    protected function setUp(): void
    {
        // Create a mock MODX object
        $this->modx = $this->createMock('MODX\Revolution\modX');
        
        // Create the command
        $this->command = new Get();
        $this->command->modx = $this->modx;
        
        // Create a command tester
        $application = new Application();
        $application->add($this->command);
        $this->commandTester = new CommandTester($this->command);
    }

    public function testConfigureHasCorrectProcessorPath()
    {
        $processor = $this->getProtectedProperty($this->command, 'processor');
        $this->assertEquals('element/category/get', $processor);
    }

    public function testConfigureHasCorrectName()
    {
        $this->assertEquals('category:get', $this->command->getName());
    }

    public function testConfigureHasCorrectDescription()
    {
        $this->assertEquals('Get a MODX category', $this->command->getDescription());
    }

    public function testExecuteWithSuccessfulResponse()
    {
        // Mock the runProcessor method to return a successful response
        $processorResponse = $this->getMockBuilder('MODX\Revolution\Processors\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $processorResponse->method('getResponse')
            ->willReturn(json_encode([
                'object' => [
                    'id' => 123,
                    'category' => 'Test Category',
                    'parent' => 0,
                    'rank' => 0
                ]
            ]));
        $processorResponse->method('isError')->willReturn(false);
        
        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->with(
                'element/category/get',
                $this->callback(function($properties) {
                    return isset($properties['id']) && $properties['id'] === '123';
                }),
                $this->anything()
            )
            ->willReturn($processorResponse);
        
        // Execute the command
        $this->commandTester->execute([
            'command' => $this->command->getName(),
            'id' => '123',
            '--format' => 'table'
        ]);
        
        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Test Category', $output);
        $this->assertStringContainsString('123', $output);
    }

    public function testExecuteWithJsonFormat()
    {
        // Mock the runProcessor method to return a successful response
        $processorResponse = $this->getMockBuilder('MODX\Revolution\Processors\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $processorResponse->method('getResponse')
            ->willReturn(json_encode([
                'object' => [
                    'id' => 123,
                    'category' => 'Test Category',
                    'parent' => 0,
                    'rank' => 0
                ]
            ]));
        $processorResponse->method('isError')->willReturn(false);
        
        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->willReturn($processorResponse);
        
        // Execute the command
        $this->commandTester->execute([
            'command' => $this->command->getName(),
            'id' => '123',
            '--format' => 'json'
        ]);
        
        // Verify the output is JSON
        $output = $this->commandTester->getDisplay();
        $this->assertJson($output);
        $data = json_decode($output, true);
        $this->assertEquals(123, $data['id']);
        $this->assertEquals('Test Category', $data['category']);
    }

    public function testExecuteWithJsonOption()
    {
        // Mock the runProcessor method to return a successful response
        $processorResponse = $this->getMockBuilder('MODX\Revolution\Processors\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $processorResponse->method('getResponse')
            ->willReturn(json_encode([
                'object' => [
                    'id' => 123,
                    'category' => 'Test Category',
                    'parent' => 0,
                    'rank' => 0
                ]
            ]));
        $processorResponse->method('isError')->willReturn(false);
        
        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->willReturn($processorResponse);
        
        // Execute the command with --json option
        $this->commandTester->execute([
            'command' => $this->command->getName(),
            'id' => '123',
            '--json' => true
        ]);
        
        // Verify the output is JSON
        $output = $this->commandTester->getDisplay();
        $this->assertJson($output);
        $data = json_decode($output, true);
        $this->assertEquals(123, $data['id']);
        $this->assertEquals('Test Category', $data['category']);
    }

    public function testExecuteWithNotFoundAndJsonOption()
    {
        // Mock the runProcessor method to return a response with no object
        $processorResponse = $this->getMockBuilder('MODX\Revolution\Processors\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $processorResponse->method('getResponse')
            ->willReturn(json_encode([]));
        $processorResponse->method('isError')->willReturn(false);
        
        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->willReturn($processorResponse);
        
        // Execute the command with --json option
        $this->commandTester->execute([
            'command' => $this->command->getName(),
            'id' => '999',
            '--json' => true
        ]);
        
        // Verify the output is JSON with error message
        $output = $this->commandTester->getDisplay();
        $this->assertJson($output);
        $data = json_decode($output, true);
        $this->assertFalse($data['success']);
        $this->assertStringContainsString('not found', $data['message']);
    }

    public function testExecuteWithCategoryNotFound()
    {
        // Mock the runProcessor method to return a response with no object
        $processorResponse = $this->getMockBuilder('MODX\Revolution\Processors\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $processorResponse->method('getResponse')
            ->willReturn(json_encode([]));
        $processorResponse->method('isError')->willReturn(false);
        
        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->willReturn($processorResponse);
        
        // Execute the command
        $this->commandTester->execute([
            'command' => $this->command->getName(),
            'id' => '999'
        ]);
        
        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Category not found', $output);
    }
}
