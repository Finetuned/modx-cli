<?php namespace MODX\CLI\Tests\Command\TV;

use MODX\CLI\Command\TV\Get;
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
        $this->assertEquals('Element\Tv\Get', $processor);
    }

    public function testConfigureHasCorrectName()
    {
        $this->assertEquals('tv:get', $this->command->getName());
    }

    public function testConfigureHasCorrectDescription()
    {
        $this->assertEquals('Get a MODX template variable', $this->command->getDescription());
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
                    'name' => 'TestTV',
                    'caption' => 'Test Caption',
                    'description' => 'Test description',
                    'category' => 1,
                    'type' => 'text',
                    'default_text' => 'Default value',
                    'elements' => '',
                    'rank' => 0,
                    'display' => 'default',
                    'locked' => 0,
                    'static' => 0,
                    'static_file' => '',
                    'template_access' => [1, 2, 3]
                ]
            ]));
        $processorResponse->method('isError')->willReturn(false);
        
        // Mock the category object
        $category = $this->getMockBuilder('MODX\Revolution\modCategory')
            ->disableOriginalConstructor()
            ->getMock();
        $category->method('get')
            ->with('category')
            ->willReturn('Test Category');
        
        // Mock the template objects
        $template1 = $this->getMockBuilder('MODX\Revolution\modTemplate')
            ->disableOriginalConstructor()
            ->getMock();
        $template1->method('get')
            ->with('templatename')
            ->willReturn('Template 1');
        
        $template2 = $this->getMockBuilder('MODX\Revolution\modTemplate')
            ->disableOriginalConstructor()
            ->getMock();
        $template2->method('get')
            ->with('templatename')
            ->willReturn('Template 2');
        
        $template3 = $this->getMockBuilder('MODX\Revolution\modTemplate')
            ->disableOriginalConstructor()
            ->getMock();
        $template3->method('get')
            ->with('templatename')
            ->willReturn('Template 3');
        
        // Mock the getObject method
        $this->modx->method('getObject')
            ->willReturnMap([
                ['modCategory', 1, $category],
                ['modTemplate', 1, $template1],
                ['modTemplate', 2, $template2],
                ['modTemplate', 3, $template3]
            ]);
        
        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->with(
                'Element\Tv\Get',
                $this->callback(function($properties) {
                    return isset($properties['id']) && $properties['id'] === '123';
                }),
                $this->anything()
            )
            ->willReturn($processorResponse);
        
        // Execute the command
        $this->commandTester->execute([
            'command' => 'tv:get',
            'id' => '123',
            '--format' => 'table'
        ]);
        
        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('TestTV', $output);
        $this->assertStringContainsString('Test Caption', $output);
        $this->assertStringContainsString('Test description', $output);
        $this->assertStringContainsString('Default value', $output);
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
                    'name' => 'TestTV',
                    'caption' => 'Test Caption',
                    'description' => 'Test description',
                    'category' => 1,
                    'type' => 'text',
                    'default_text' => 'Default value',
                    'elements' => '',
                    'rank' => 0,
                    'display' => 'default',
                    'locked' => 0,
                    'static' => 0,
                    'static_file' => ''
                ]
            ]));
        $processorResponse->method('isError')->willReturn(false);
        
        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->willReturn($processorResponse);
        
        // Execute the command
        $this->commandTester->execute([
            'command' => 'tv:get',
            'id' => '123',
            '--format' => 'json'
        ]);
        
        // Verify the output is JSON
        $output = $this->commandTester->getDisplay();
        $this->assertJson($output);
        $data = json_decode($output, true);
        $this->assertEquals(123, $data['id']);
        $this->assertEquals('TestTV', $data['name']);
        $this->assertEquals('Test Caption', $data['caption']);
        $this->assertEquals('Default value', $data['default_text']);
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
                    'name' => 'TestTV',
                    'caption' => 'Test Caption',
                    'description' => 'Test description',
                    'category' => 1,
                    'type' => 'text',
                    'default_text' => 'Default value',
                    'elements' => '',
                    'rank' => 0,
                    'display' => 'default',
                    'locked' => 0,
                    'static' => 0,
                    'static_file' => ''
                ]
            ]));
        $processorResponse->method('isError')->willReturn(false);
        
        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->willReturn($processorResponse);
        
        // Execute the command with --json option
        $this->commandTester->execute([
            'command' => 'tv:get',
            'id' => '123',
            '--json' => true
        ]);
        
        // Verify the output is JSON
        $output = $this->commandTester->getDisplay();
        $this->assertJson($output);
        $data = json_decode($output, true);
        $this->assertEquals(123, $data['id']);
        $this->assertEquals('TestTV', $data['name']);
        $this->assertEquals('Test Caption', $data['caption']);
        $this->assertEquals('Default value', $data['default_text']);
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
            'command' => 'tv:get',
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

    public function testExecuteWithTVNotFound()
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
            'command' => 'tv:get',
            'id' => '999'
        ]);
        
        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Template variable not found', $output);
    }
}
