<?php namespace MODX\CLI\Tests\Command\User;

use MODX\CLI\Command\User\GetList;
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
        $this->assertEquals('Security\User\GetList', $processor);
    }

    public function testConfigureHasCorrectName()
    {
        $this->assertEquals('user:list', $this->command->getName());
    }

    public function testConfigureHasCorrectDescription()
    {
        $this->assertEquals('Get a list of users in MODX', $this->command->getDescription());
    }

    public function testConfigureHasCorrectHeaders()
    {
        $headers = $this->getProtectedProperty($this->command, 'headers');
        $this->assertIsArray($headers);
        $this->assertContains('id', $headers);
        $this->assertContains('username', $headers);
        $this->assertContains('fullname', $headers);
        $this->assertContains('email', $headers);
        $this->assertContains('active', $headers);
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
                    ['id' => 1, 'username' => 'admin', 'fullname' => 'Administrator', 'email' => 'admin@example.com', 'active' => 1],
                    ['id' => 2, 'username' => 'user1', 'fullname' => 'User One', 'email' => 'user1@example.com', 'active' => 0],
                ]
            ]));
        $processorResponse->method('isError')->willReturn(false);
        
        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->with('Security\User\GetList')
            ->willReturn($processorResponse);
        
        // Execute the command
        $this->commandTester->execute([
            
        ]);
        
        // Verify the output contains user data
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('admin', $output);
        $this->assertStringContainsString('user1', $output);
    }

    public function testExecuteWithJsonOption()
    {
        // Mock the runProcessor method to return a successful response
        $processorResponse = $this->getMockBuilder('MODX\Revolution\Processors\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $processorResponse->method('getResponse')
            ->willReturn(json_encode([
                'success' => true,
                'results' => [
                    ['id' => 1, 'username' => 'admin', 'fullname' => 'Administrator', 'email' => 'admin@example.com', 'active' => 1],
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
        
        // Verify the output is valid JSON
        $output = $this->commandTester->getDisplay();
        $decoded = json_decode($output, true);
        $this->assertNotNull($decoded);
    }

    public function testExecuteWithActiveFilter()
    {
        // Mock the runProcessor method
        $processorResponse = $this->getMockBuilder('MODX\Revolution\Processors\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $processorResponse->method('getResponse')
            ->willReturn(json_encode([
                'success' => true,
                'results' => [
                    ['id' => 1, 'username' => 'admin', 'fullname' => 'Administrator', 'email' => 'admin@example.com', 'active' => 1],
                ]
            ]));
        $processorResponse->method('isError')->willReturn(false);
        
        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->with(
                'Security\User\GetList',
                $this->callback(function($properties) {
                    return isset($properties['active']) && $properties['active'] === '1';
                }),
                $this->anything()
            )
            ->willReturn($processorResponse);
        
        // Execute the command with --active filter
        $this->commandTester->execute([
            
            '--active' => '1',
        ]);
        
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('admin', $output);
    }

    public function testExecuteWithQueryFilter()
    {
        // Mock the runProcessor method
        $processorResponse = $this->getMockBuilder('MODX\Revolution\Processors\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $processorResponse->method('getResponse')
            ->willReturn(json_encode([
                'success' => true,
                'results' => [
                    ['id' => 1, 'username' => 'admin', 'fullname' => 'Administrator', 'email' => 'admin@example.com', 'active' => 1],
                ]
            ]));
        $processorResponse->method('isError')->willReturn(false);
        
        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->with(
                'Security\User\GetList',
                $this->callback(function($properties) {
                    return isset($properties['query']) && $properties['query'] === 'admin';
                }),
                $this->anything()
            )
            ->willReturn($processorResponse);
        
        // Execute the command with --query filter
        $this->commandTester->execute([
            
            '--query' => 'admin',
        ]);
        
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('admin', $output);
    }
}
