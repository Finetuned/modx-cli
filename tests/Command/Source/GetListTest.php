<?php

namespace MODX\CLI\Tests\Command\Source;

use MODX\CLI\Command\Source\GetList;
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
        $this->assertEquals('Source\GetList', $processor);
    }

    public function testConfigureHasCorrectName()
    {
        $this->assertEquals('source:list', $this->command->getName());
    }

    public function testConfigureHasCorrectDescription()
    {
        $this->assertEquals('Get a list of media sources in MODX', $this->command->getDescription());
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
                        'name' => 'Filesystem',
                        'description' => 'Default filesystem source',
                        'class_key' => 'sources.modFileMediaSource'
                    ],
                    [
                        'id' => '2',
                        'name' => 'S3 Bucket',
                        'description' => 'Amazon S3 storage',
                        'class_key' => 'sources.modS3MediaSource'
                    ]
                ],
                'total' => 2
            ]));
        $processorResponse->method('isError')->willReturn(false);
        
        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->with('Source\GetList')
            ->willReturn($processorResponse);
        
        // Execute the command
        $this->commandTester->execute([]);
        
        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Filesystem', $output);
        $this->assertStringContainsString('S3 Bucket', $output);
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
        $processorResponse = $this->getMockBuilder('MODX\\\\\\\\Revolution\\\\\\\\Processors\\\\\\\\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $processorResponse->method('getResponse')
            ->willReturn(json_encode([
                'success' => false,
                'message' => 'Error fetching media sources',
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

    public function testExecuteWithPaginationOptions()
    {
        // Mock the runProcessor method
        $processorResponse = $this->getMockBuilder('MODX\Revolution\Processors\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $processorResponse->method('getResponse')
            ->willReturn(json_encode([
                'success' => true,
                'results' => [
                    ['id' => '1', 'name' => 'Source 1']
                ],
                'total' => 10
            ]));
        $processorResponse->method('isError')->willReturn(false);
        
        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->with('Source\GetList', $this->callback(function($properties) {
                return isset($properties['limit']) && $properties['limit'] === 5 &&
                       isset($properties['start']) && $properties['start'] === 10;
            }))
            ->willReturn($processorResponse);
        
        // Execute the command with pagination
        $this->commandTester->execute([
            '--limit' => '5',
            '--start' => '10'
        ]);
        
        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }
}
