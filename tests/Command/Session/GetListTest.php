<?php

namespace MODX\CLI\Tests\Command\Session;

use MODX\CLI\Command\Session\GetList;
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

    public function testConfigureHasCorrectProcessor()
    {
        $processor = $this->getProtectedProperty($this->command, 'processor');
        $this->assertEquals('Security\Session\GetList', $processor);
    }

    public function testConfigureHasCorrectName()
    {
        $this->assertEquals('session:list', $this->command->getName());
    }

    public function testConfigureHasCorrectDescription()
    {
        $this->assertEquals('Get a list of sessions in MODX', $this->command->getDescription());
    }

    public function testConfigureHasCorrectHeaders()
    {
        $headers = $this->getProtectedProperty($this->command, 'headers');
        $this->assertIsArray($headers);
        $this->assertContains('id', $headers);
        $this->assertContains('username', $headers);
        $this->assertContains('ip', $headers);
        $this->assertContains('access', $headers);
        $this->assertContains('last_hit', $headers);
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
                    [
                        'id' => '1',
                        'username' => 'admin',
                        'ip' => '127.0.0.1',
                        'access' => '1698768000',
                        'last_hit' => '1698768000'
                    ]
                ],
                'total' => 1
            ]));
        $processorResponse->method('isError')->willReturn(false);
        
        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->with('Security\Session\GetList')
            ->willReturn($processorResponse);
        
        // Execute the command
        $this->commandTester->execute([]);
        
        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('admin', $output);
        $this->assertStringContainsString('127.0.0.1', $output);
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
                'results' => [],
                'total' => 0
            ]));
        $processorResponse->method('isError')->willReturn(false);
        
        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->willReturn($processorResponse);
        
        // Execute the command
        $this->commandTester->execute([]);
        
        // Verify the output shows 0 items
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('displaying 0 item(s) of 0', $output);
    }

    public function testExecuteWithFailedResponse()
    {
        // Mock the runProcessor method to return a failed response
        $processorResponse = $this->getMockBuilder('MODX\\\\Revolution\\\\Processors\\\\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $processorResponse->method('getResponse')
            ->willReturn(json_encode([
                'success' => false,
                'message' => 'Error fetching sessions',
                'results' => [],
                'total' => 0
            ]));
        $processorResponse->method('isError')->willReturn(true);
        
        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->willReturn($processorResponse);
        
        // Execute the command
        $this->commandTester->execute([]);
        
        // Verify the output - ListProcessor displays empty table for failed responses without field errors
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('displaying 0 item(s) of 0', $output);
    }

    public function testParseValueFormatsTimestamps()
    {
        // Use reflection to call protected parseValue method
        $reflection = new \ReflectionClass($this->command);
        $method = $reflection->getMethod('parseValue');
        $method->setAccessible(true);
        
        // Test timestamp formatting for 'access' column
        $timestamp = '1698768000';
        $result = $method->invoke($this->command, $timestamp, 'access');
        $this->assertEquals('2023-10-31 16:00:00', $result);
        
        // Test timestamp formatting for 'last_hit' column
        $result = $method->invoke($this->command, $timestamp, 'last_hit');
        $this->assertEquals('2023-10-31 16:00:00', $result);
        
        // Test non-timestamp column
        $result = $method->invoke($this->command, 'admin', 'username');
        $this->assertEquals('admin', $result);
    }

    public function testParseValueHandlesEmptyTimestamps()
    {
        // Use reflection to call protected parseValue method
        $reflection = new \ReflectionClass($this->command);
        $method = $reflection->getMethod('parseValue');
        $method->setAccessible(true);
        
        // Test empty timestamp for 'access' column
        $result = $method->invoke($this->command, '', 'access');
        $this->assertEquals('', $result);
        
        // Test null timestamp for 'last_hit' column
        $result = $method->invoke($this->command, null, 'last_hit');
        $this->assertNull($result);
    }
}
