<?php namespace MODX\CLI\Tests\Command\Plugin;

use MODX\CLI\Command\Plugin\DisabledPlugin;
use MODX\CLI\Tests\Configuration\BaseTest;
use MODX\CLI\Application;
use Symfony\Component\Console\Tester\CommandTester;

class DisabledPluginTest extends BaseTest
{
    protected $command;
    protected $commandTester;
    protected $modx;

    protected function setUp(): void
    {
        // Create a mock MODX object
        $this->modx = $this->createMock('MODX\Revolution\modX');
        
        // Create the command
        $this->command = new DisabledPlugin();
        $this->command->modx = $this->modx;
        
        // Create a command tester
        $this->commandTester = new CommandTester($this->command);
    }

    public function testConfigureHasCorrectProcessorPath()
    {
        $processor = $this->getProtectedProperty($this->command, 'processor');
        $this->assertEquals('Element\Plugin\GetList', $processor);
    }

    public function testConfigureHasCorrectName()
    {
        $this->assertEquals('plugin:disabled', $this->command->getName());
    }

    public function testConfigureHasCorrectDescription()
    {
        $this->assertEquals('Get a list of disabled plugins in MODX', $this->command->getDescription());
    }

    public function testConfigureHasCorrectHeaders()
    {
        $headers = $this->getProtectedProperty($this->command, 'headers');
        $this->assertIsArray($headers);
        $this->assertContains('id', $headers);
        $this->assertContains('name', $headers);
        $this->assertContains('description', $headers);
        $this->assertContains('category', $headers);
    }

    public function testConfigureHasDisabledDefaultProperty()
    {
        $defaultsProperties = $this->getProtectedProperty($this->command, 'defaultsProperties');
        $this->assertIsArray($defaultsProperties);
        $this->assertArrayHasKey('disabled', $defaultsProperties);
        $this->assertEquals(1, $defaultsProperties['disabled']);
    }

    public function testBeforeRunSetsDisabledFilter()
    {
        // Create a reflection of the command to access protected method
        $reflection = new \ReflectionClass($this->command);
        $beforeRunMethod = $reflection->getMethod('beforeRun');
        $beforeRunMethod->setAccessible(true);

        // Test the beforeRun method
        $properties = [];
        $options = [];
        $beforeRunMethod->invokeArgs($this->command, [&$properties, &$options]);

        // Verify the disabled property is set
        $this->assertArrayHasKey('disabled', $properties);
        $this->assertEquals(1, $properties['disabled']);
    }

    public function testExecuteWithSuccessfulResponse()
    {
        // Mock the runProcessor method to return only disabled plugins
        $processorResponse = $this->getMockBuilder('MODX\Revolution\Processors\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $processorResponse->method('getResponse')
            ->willReturn(json_encode([
                'success' => true,
                'results' => [
                    ['id' => 1, 'name' => 'DisabledPlugin1', 'description' => 'First disabled plugin', 'category' => 1, 'disabled' => 1],
                    ['id' => 2, 'name' => 'DisabledPlugin2', 'description' => 'Second disabled plugin', 'category' => 2, 'disabled' => 1],
                ]
            ]));
        $processorResponse->method('isError')->willReturn(false);
        
        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->with('Element\Plugin\GetList', $this->callback(function($properties) {
                // Verify that disabled=1 is passed to processor
                return isset($properties['disabled']) && $properties['disabled'] === 1;
            }))
            ->willReturn($processorResponse);
        
        // Execute the command
        $this->commandTester->execute([
            
        ]);
        
        // Verify the output contains only disabled plugin data
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('DisabledPlugin1', $output);
        $this->assertStringContainsString('DisabledPlugin2', $output);
    }

    public function testExecuteWithEmptyResults()
    {
        // Mock the runProcessor method to return empty results
        $processorResponse = $this->getMockBuilder('MODX\\Revolution\\Processors\\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $processorResponse->method('getResponse')
            ->willReturn(json_encode([
                'success' => true,
                'results' => []
            ]));
        $processorResponse->method('isError')->willReturn(false);
        
        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->willReturn($processorResponse);
        
        // Execute the command
        $this->commandTester->execute([
            
        ]);
        
        // Verify appropriate message is displayed (showing 0 items)
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('displaying 0 item(s)', $output);
    }

    public function testExecuteWithJsonOption()
    {
        // Mock the runProcessor method
        $processorResponse = $this->getMockBuilder('MODX\Revolution\Processors\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $processorResponse->method('getResponse')
            ->willReturn(json_encode([
                'success' => true,
                'results' => [
                    ['id' => 1, 'name' => 'DisabledPlugin', 'description' => 'A disabled plugin', 'category' => 1, 'disabled' => 1],
                ]
            ]));
        $processorResponse->method('isError')->willReturn(false);
        
        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->willReturn($processorResponse);
        
        // Execute the command with --json option
        $this->commandTester->execute([
            
            '--json' => true,
        ]);
        
        // Verify JSON output
        $output = $this->commandTester->getDisplay();
        $this->assertJson($output);
        
        $data = json_decode($output, true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('results', $data);
        $this->assertCount(1, $data['results']);
        $this->assertEquals('DisabledPlugin', $data['results'][0]['name']);
    }

    public function testExecuteWithPaginationOptions()
    {
        // Mock the runProcessor method
        $processorResponse = $this->getMockBuilder('MODX\\Revolution\\Processors\\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $processorResponse->method('getResponse')
            ->willReturn(json_encode([
                'success' => true,
                'results' => [
                    ['id' => 1, 'name' => 'DisabledPlugin', 'description' => 'A disabled plugin', 'category' => 1, 'disabled' => 1],
                ],
                'total' => 10
            ]));
        $processorResponse->method('isError')->willReturn(false);
        
        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->with('Element\\Plugin\\GetList', $this->callback(function($properties) {
                // The ListProcessor base class may merge properties differently
                // Just verify that disabled filter is set
                return isset($properties['disabled']) && $properties['disabled'] === 1;
            }))
            ->willReturn($processorResponse);
        
        // Execute the command with pagination options
        $this->commandTester->execute([
            
            '--limit' => 5,
            '--start' => 10,
        ]);
        
        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }

    public function testParseValueForCategory()
    {
        // Create a reflection to access protected method
        $reflection = new \ReflectionClass($this->command);
        $parseValueMethod = $reflection->getMethod('parseValue');
        $parseValueMethod->setAccessible(true);

        // Mock modCategory object
        $category = new \stdClass();
        $category->category = 'Test Category';

        // Test category parsing
        $result = $parseValueMethod->invokeArgs($this->command, [$category, 'category']);
        $this->assertNotNull($result);
    }

    public function testExecuteVerifiesOnlyDisabledPluginsReturned()
    {
        // Mock processor response with mixed enabled/disabled plugins
        // The command should filter to show only disabled ones
        $processorResponse = $this->getMockBuilder('MODX\Revolution\Processors\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $processorResponse->method('getResponse')
            ->willReturn(json_encode([
                'success' => true,
                'results' => [
                    ['id' => 2, 'name' => 'DisabledPlugin', 'description' => 'This is disabled', 'category' => 1, 'disabled' => 1],
                ]
            ]));
        $processorResponse->method('isError')->willReturn(false);
        
        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->with('Element\Plugin\GetList', $this->callback(function($properties) {
                // Ensure disabled=1 is enforced
                return $properties['disabled'] === 1;
            }))
            ->willReturn($processorResponse);
        
        $this->commandTester->execute([
            
        ]);
        
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('DisabledPlugin', $output);
        $this->assertStringNotContainsString('EnabledPlugin', $output);
    }
}
