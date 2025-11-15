<?php namespace MODX\CLI\Tests\Command\Ns;

use MODX\CLI\Command\Ns\GetList;
use MODX\CLI\Tests\Configuration\BaseTest;
use Symfony\Component\Console\Tester\CommandTester;

class GetListTest extends BaseTest
{
    protected $command;
    protected $commandTester;
    protected $modx;

    protected function setUp(): void
    {
        // Create a mock MODX object
        $this->modx = $this->createMock('MODX\Revolution\modX');
        
        // Create the command
        $this->command = new GetList();
        $this->command->modx = $this->modx;
        
        // Create a command tester
        $this->commandTester = new CommandTester($this->command);
    }

    public function testConfigureHasCorrectProcessorPath()
    {
        $processor = $this->getProtectedProperty($this->command, 'processor');
        $this->assertEquals('Workspace\PackageNamespace\GetList', $processor);
    }

    public function testConfigureHasCorrectName()
    {
        $this->assertEquals('ns:list', $this->command->getName());
    }

    public function testConfigureHasCorrectDescription()
    {
        $this->assertEquals('Get a list of namespaces in MODX', $this->command->getDescription());
    }

    public function testConfigureHasIdInHeaders()
    {
        $headers = $this->getProtectedProperty($this->command, 'headers');
        $this->assertContains('id', $headers);
        $this->assertContains('name', $headers);
        $this->assertContains('path', $headers);
        $this->assertContains('assets_path', $headers);
    }

    public function testExecuteWithSuccessfulResponse()
    {
        // Mock the runProcessor method to return a successful response
        $processorResponse = $this->getMockBuilder('MODX\Revolution\Processors\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        
        $namespaces = [
            ['id' => 1, 'name' => 'core', 'path' => '{core_path}', 'assets_path' => '{assets_path}'],
            ['id' => 2, 'name' => 'test', 'path' => '{base_path}test/', 'assets_path' => '{assets_path}test/']
        ];
        
        $processorResponse->method('getResponse')
            ->willReturn(json_encode([
                'success' => true,
                'total' => 2,
                'results' => $namespaces
            ]));
        $processorResponse->method('isError')->willReturn(false);
        
        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->with('Workspace\PackageNamespace\GetList')
            ->willReturn($processorResponse);
        
        // Execute the command
        $this->commandTester->execute([]);
        
        // Verify the output contains namespace data
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('core', $output);
        $this->assertStringContainsString('test', $output);
    }

    public function testExecuteWithJsonOption()
    {
        // Mock the runProcessor method to return a successful response
        $processorResponse = $this->getMockBuilder('MODX\Revolution\Processors\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        
        $namespaces = [
            ['id' => 1, 'name' => 'core', 'path' => '{core_path}', 'assets_path' => '{assets_path}'],
            ['id' => 2, 'name' => 'test', 'path' => '{base_path}test/', 'assets_path' => '{assets_path}test/']
        ];
        
        $processorResponse->method('getResponse')
            ->willReturn(json_encode([
                'success' => true,
                'total' => 2,
                'results' => $namespaces
            ]));
        $processorResponse->method('isError')->willReturn(false);
        
        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->willReturn($processorResponse);
        
        // Execute the command with --json option
        $this->commandTester->execute(['--json' => true]);
        
        // Verify the output is valid JSON
        $output = $this->commandTester->getDisplay();
        $json = json_decode($output, true);
        
        $this->assertIsArray($json);
        $this->assertEquals(2, $json['total']);
        $this->assertCount(2, $json['results']);
        $this->assertEquals('core', $json['results'][0]['name']);
    }

    public function testExecuteWithEmptyResults()
    {
        // Mock the runProcessor method to return an empty response
        $processorResponse = $this->getMockBuilder('MODX\Revolution\Processors\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        
        $processorResponse->method('getResponse')
            ->willReturn(json_encode([
                'success' => true,
                'total' => 0,
                'results' => []
            ]));
        $processorResponse->method('isError')->willReturn(false);
        
        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->willReturn($processorResponse);
        
        // Execute the command
        $this->commandTester->execute([]);
        
        // Command should execute successfully even with no results
        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }

    public function testExecuteWithPaginationOptions()
    {
        // Mock the runProcessor method
        $processorResponse = $this->getMockBuilder('MODX\Revolution\Processors\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        
        $namespaces = [
            ['id' => 11, 'name' => 'namespace11', 'path' => '{base_path}ns11/', 'assets_path' => '{assets_path}ns11/']
        ];
        
        $processorResponse->method('getResponse')
            ->willReturn(json_encode([
                'success' => true,
                'total' => 20,
                'results' => $namespaces
            ]));
        $processorResponse->method('isError')->willReturn(false);
        
        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->with(
                'Workspace\PackageNamespace\GetList',
                $this->callback(function($properties) {
                    return isset($properties['limit']) && $properties['limit'] === 5 &&
                           isset($properties['start']) && $properties['start'] === 10;
                }),
                $this->anything()
            )
            ->willReturn($processorResponse);
        
        // Execute the command with pagination options
        $this->commandTester->execute([
            '--limit' => 5,
            '--start' => 10
        ]);
        
        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }
}
