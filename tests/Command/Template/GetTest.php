<?php namespace MODX\CLI\Tests\Command\Template;

use MODX\CLI\Command\Template\Get;
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
        $this->assertEquals('element/template/get', $processor);
    }

    public function testConfigureHasCorrectName()
    {
        $this->assertEquals('template:get', $this->command->getName());
    }

    public function testConfigureHasCorrectDescription()
    {
        $this->assertEquals('Get a MODX template', $this->command->getDescription());
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
                    'templatename' => 'TestTemplate',
                    'description' => 'Test description',
                    'category' => 1,
                    'locked' => 0,
                    'static' => 0,
                    'static_file' => '',
                    'icon' => '',
                    'content' => '<html><body>[[*content]]</body></html>'
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
        
        // Mock the getObject method
        $this->modx->method('getObject')
            ->with('modCategory', 1)
            ->willReturn($category);
        
        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->with(
                'element/template/get',
                $this->callback(function($properties) {
                    return isset($properties['id']) && $properties['id'] === '123';
                }),
                $this->anything()
            )
            ->willReturn($processorResponse);
        
        // Execute the command
        $this->commandTester->execute([
            'command' => 'template:get',
            'id' => '123',
            '--format' => 'table'
        ]);
        
        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('TestTemplate', $output);
        $this->assertStringContainsString('Test description', $output);
        $this->assertStringContainsString('[[*content]]', $output);
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
                    'templatename' => 'TestTemplate',
                    'description' => 'Test description',
                    'category' => 1,
                    'locked' => 0,
                    'static' => 0,
                    'static_file' => '',
                    'icon' => '',
                    'content' => '<html><body>[[*content]]</body></html>'
                ]
            ]));
        $processorResponse->method('isError')->willReturn(false);
        
        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->willReturn($processorResponse);
        
        // Execute the command
        $this->commandTester->execute([
            'command' => 'template:get',
            'id' => '123',
            '--format' => 'json'
        ]);
        
        // Verify the output is JSON
        $output = $this->commandTester->getDisplay();
        $this->assertJson($output);
        $data = json_decode($output, true);
        $this->assertEquals(123, $data['id']);
        $this->assertEquals('TestTemplate', $data['templatename']);
        $this->assertEquals('<html><body>[[*content]]</body></html>', $data['content']);
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
                    'templatename' => 'TestTemplate',
                    'description' => 'Test description',
                    'category' => 1,
                    'locked' => 0,
                    'static' => 0,
                    'static_file' => '',
                    'icon' => '',
                    'content' => '<html><body>[[*content]]</body></html>'
                ]
            ]));
        $processorResponse->method('isError')->willReturn(false);
        
        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->willReturn($processorResponse);
        
        // Execute the command with --json option
        $this->commandTester->execute([
            'command' => 'template:get',
            'id' => '123',
            '--json' => true
        ]);
        
        // Verify the output is JSON
        $output = $this->commandTester->getDisplay();
        $this->assertJson($output);
        $data = json_decode($output, true);
        $this->assertEquals(123, $data['id']);
        $this->assertEquals('TestTemplate', $data['templatename']);
        $this->assertEquals('<html><body>[[*content]]</body></html>', $data['content']);
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
            'command' => 'template:get',
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

    public function testExecuteWithTemplateNotFound()
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
            'command' => 'template:get',
            'id' => '999'
        ]);
        
        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Template not found', $output);
    }
}
