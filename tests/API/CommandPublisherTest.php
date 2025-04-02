<?php

namespace MODX\CLI\Tests\API;

use MODX\CLI\API\CommandPublisher;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class CommandPublisherTest extends TestCase
{
    /**
     * @var CommandPublisher
     */
    private $publisher;
    
    protected function setUp(): void
    {
        $this->publisher = new CommandPublisher();
    }
    
    public function testPublish()
    {
        $command = 'test:command';
        $callback = function ($result) {
            return $result;
        };
        
        // Use reflection to access the private property
        $reflection = new \ReflectionClass($this->publisher);
        $property = $reflection->getProperty('subscribers');
        $property->setAccessible(true);
        
        // Call the publish method
        $this->publisher->publish($command, $callback);
        
        // Get the subscribers array
        $subscribers = $property->getValue($this->publisher);
        
        $this->assertCount(1, $subscribers);
        $this->assertEquals($command, $subscribers[0]['command']);
        $this->assertSame($callback, $subscribers[0]['callback']);
    }
    
    public function testRunWithNoSubscribers()
    {
        // This should not throw any exceptions
        $this->publisher->run();
        
        $this->assertTrue(true); // If we got here, the test passed
    }
    
    public function testRunWithSuccessfulProcess()
    {
        // This test is more of an integration test and requires mocking Process
        // We'll use a simpler approach to test the basic functionality
        
        // Set up a test callback
        $callbackCalled = false;
        $callbackResult = null;
        $callback = function ($result) use (&$callbackCalled, &$callbackResult) {
            $callbackCalled = true;
            $callbackResult = $result;
            return $result;
        };
        
        // Create a mock for Process class
        $processMock = $this->createMock(Process::class);
        $processMock->method('isRunning')->willReturn(false);
        $processMock->method('isSuccessful')->willReturn(true);
        $processMock->method('getOutput')->willReturn('Command output');
        
        // Create a mock for CommandPublisher
        $publisher = $this->getMockBuilder(CommandPublisher::class)
            ->onlyMethods(['createProcess'])
            ->getMock();
        
        // Configure the mock to return our process mock
        $publisher->method('createProcess')
            ->willReturn($processMock);
        
        // Add a subscriber
        $publisher->publish('test:command', $callback);
        
        // Run the publisher
        $publisher->run();
        
        // Check that the callback was called with the expected result
        $this->assertTrue($callbackCalled);
        $this->assertTrue($callbackResult['success']);
        $this->assertEquals('Command output', $callbackResult['data']);
    }
    
    public function testRunWithFailedProcess()
    {
        // This test is more of an integration test and requires mocking Process
        // We'll use a simpler approach to test the basic functionality
        
        // Set up a test callback
        $callbackCalled = false;
        $callbackResult = null;
        $callback = function ($result) use (&$callbackCalled, &$callbackResult) {
            $callbackCalled = true;
            $callbackResult = $result;
            return $result;
        };
        
        // Create a mock for Process class
        $processMock = $this->createMock(Process::class);
        $processMock->method('isRunning')->willReturn(false);
        $processMock->method('isSuccessful')->willReturn(false);
        $processMock->method('getErrorOutput')->willReturn('Command error');
        
        // Create a mock for CommandPublisher
        $publisher = $this->getMockBuilder(CommandPublisher::class)
            ->onlyMethods(['createProcess'])
            ->getMock();
        
        // Configure the mock to return our process mock
        $publisher->method('createProcess')
            ->willReturn($processMock);
        
        // Add a subscriber
        $publisher->publish('test:command', $callback);
        
        // Run the publisher
        $publisher->run();
        
        // Check that the callback was called with the expected result
        $this->assertTrue($callbackCalled);
        $this->assertFalse($callbackResult['success']);
        $this->assertEquals('Command error', $callbackResult['error']);
    }
    
    public function testRunWithMultipleProcesses()
    {
        // This test is more of an integration test and requires mocking Process
        // We'll use a simpler approach to test the basic functionality
        
        // Set up test callbacks
        $callbacksCalled = [false, false];
        $callbackResults = [null, null];
        $callbacks = [
            function ($result) use (&$callbacksCalled, &$callbackResults) {
                $callbacksCalled[0] = true;
                $callbackResults[0] = $result;
                return $result;
            },
            function ($result) use (&$callbacksCalled, &$callbackResults) {
                $callbacksCalled[1] = true;
                $callbackResults[1] = $result;
                return $result;
            }
        ];
        
        // Create mocks for Process class
        $processMock1 = $this->createMock(Process::class);
        $processMock1->method('isRunning')->willReturn(false);
        $processMock1->method('isSuccessful')->willReturn(true);
        $processMock1->method('getOutput')->willReturn('Command 1 output');
        
        $processMock2 = $this->createMock(Process::class);
        $processMock2->method('isRunning')->willReturn(false);
        $processMock2->method('isSuccessful')->willReturn(false);
        $processMock2->method('getErrorOutput')->willReturn('Command 2 error');
        
        // Create a mock for CommandPublisher
        $publisher = $this->getMockBuilder(CommandPublisher::class)
            ->onlyMethods(['createProcess'])
            ->getMock();
        
        // Configure the mock to return different process mocks for different calls
        $publisher->expects($this->exactly(2))
            ->method('createProcess')
            ->willReturnOnConsecutiveCalls($processMock1, $processMock2);
        
        // Add subscribers
        $publisher->publish('test:command1', $callbacks[0]);
        $publisher->publish('test:command2', $callbacks[1]);
        
        // Run the publisher
        $publisher->run();
        
        // Check that both callbacks were called with the expected results
        $this->assertTrue($callbacksCalled[0]);
        $this->assertTrue($callbacksCalled[1]);
        $this->assertTrue($callbackResults[0]['success']);
        $this->assertEquals('Command 1 output', $callbackResults[0]['data']);
        $this->assertFalse($callbackResults[1]['success']);
        $this->assertEquals('Command 2 error', $callbackResults[1]['error']);
    }
}
