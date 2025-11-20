<?php namespace MODX\CLI\Tests\Command\Menu;

use MODX\CLI\Command\Menu\GetList;
use MODX\CLI\Tests\Configuration\BaseTest;
use MODX\CLI\Application;
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
        $this->assertEquals('System\Menu\GetList', $processor);
    }

    public function testConfigureHasCorrectName()
    {
        $this->assertEquals('menu:list', $this->command->getName());
    }

    public function testConfigureHasCorrectDescription()
    {
        $this->assertEquals('Get a list of menus in MODX', $this->command->getDescription());
    }

    public function testConfigureHasCorrectHeaders()
    {
        $headers = $this->getProtectedProperty($this->command, 'headers');
        $this->assertIsArray($headers);
        $this->assertContains('id', $headers);
        $this->assertContains('text', $headers);
        $this->assertContains('parent', $headers);
        $this->assertContains('action', $headers);
        $this->assertContains('namespace', $headers);
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
                'results' => [
                    ['id' => 1, 'text' => 'Dashboard', 'parent' => 0, 'action' => 'home', 'namespace' => 'core'],
                    ['id' => 2, 'text' => 'Content', 'parent' => 0, 'action' => '', 'namespace' => 'core'],
                ]
            ]));
        $processorResponse->method('isError')->willReturn(false);
        
        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->with('System\Menu\GetList')
            ->willReturn($processorResponse);
        
        // Execute the command
        $this->commandTester->execute([
            
        ]);
        
        // Verify the output contains menu data
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Dashboard', $output);
        $this->assertStringContainsString('Content', $output);
    }
}
