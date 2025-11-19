<?php

namespace MODX\CLI\Tests\Command\Registry\Message;

use MODX\CLI\Command\Registry\Message\GetList;
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
        $this->assertEquals('Registry\\Message\\GetList', $processor);
    }

    public function testConfigureHasCorrectName()
    {
        $this->assertEquals('registry:message:list', $this->command->getName());
    }

    public function testConfigureHasCorrectDescription()
    {
        $this->assertEquals('Get a list of registry messages in MODX', $this->command->getDescription());
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
                        'topic' => 'test-topic',
                        'message' => 'Test message content',
                        'created' => '2025-01-01 10:00:00'
                    ]
                ],
                'total' => 1
            ]));
        $processorResponse->method('isError')->willReturn(false);
        
        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->with('Registry\\Message\\GetList')
            ->willReturn($processorResponse);
        
        // Execute the command with required topic argument
        $this->commandTester->execute([
            'topic' => 'test-topic'
        ]);
        
        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('test-topic', $output);
        $this->assertStringContainsString('Test message content', $output);
    }

    public function testExecuteWithEmptyResults()
    {
        // Mock the runProcessor method to return empty results
        $processorResponse = $this->getMockBuilder('MODX\Revolution\Processors\ProcessorResponse')
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
        
        // Execute the command with required topic argument
        $this->commandTester->execute([
            'topic' => 'test-topic'
        ]);
        
        // Verify the output shows 0 items
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('displaying 0 item(s) of 0', $output);
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
                'message' => 'Error fetching registry messages',
                'results' => [],
                'total' => 0
            ]));
        $processorResponse->method('isError')->willReturn(true);
        
        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->willReturn($processorResponse);
        
        // Execute the command with required topic argument
        $this->commandTester->execute([
            'topic' => 'test-topic'
        ]);
        
        // Verify the output - ListProcessor displays empty table for failed responses without field errors
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('displaying 0 item(s) of 0', $output);
    }

    public function testExecuteWithTopicFilter()
    {
        // Mock the runProcessor method
        $processorResponse = $this->getMockBuilder('MODX\Revolution\Processors\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $processorResponse->method('getResponse')
            ->willReturn(json_encode([
                'success' => true,
                'results' => [
                    ['id' => '1', 'topic' => 'specific-topic', 'message' => 'Filtered message']
                ],
                'total' => 1
            ]));
        $processorResponse->method('isError')->willReturn(false);
        
        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->with('Registry\\Message\\GetList', $this->callback(function($properties) {
                return isset($properties['topic']) && $properties['topic'] === 'specific-topic';
            }))
            ->willReturn($processorResponse);
        
        // Execute the command with topic argument
        $this->commandTester->execute([
            'topic' => 'specific-topic'
        ]);
        
        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }

    public function testExecuteWithMultipleMessages()
    {
        // Mock the runProcessor method with multiple messages
        $processorResponse = $this->getMockBuilder('MODX\Revolution\Processors\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $processorResponse->method('getResponse')
            ->willReturn(json_encode([
                'success' => true,
                'results' => [
                    [
                        'id' => '1',
                        'topic' => 'topic1',
                        'message' => 'Message 1',
                        'created' => '2025-01-01 10:00:00'
                    ],
                    [
                        'id' => '2',
                        'topic' => 'topic2',
                        'message' => 'Message 2',
                        'created' => '2025-01-02 10:00:00'
                    ]
                ],
                'total' => 2
            ]));
        $processorResponse->method('isError')->willReturn(false);
        
        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->willReturn($processorResponse);
        
        // Execute the command with required topic argument
        $this->commandTester->execute([
            'topic' => 'test-topic'
        ]);
        
        // Verify both messages are in output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('topic1', $output);
        $this->assertStringContainsString('topic2', $output);
        $this->assertStringContainsString('Message 1', $output);
        $this->assertStringContainsString('Message 2', $output);
    }
}
