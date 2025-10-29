<?php namespace MODX\CLI\Tests\Command\Package;

use MODX\CLI\Command\Package\GetList;
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
        $application = new Application();
        $application->add($this->command);
        $this->commandTester = new CommandTester($this->command);
    }

    public function testConfigureHasCorrectProcessorPath()
    {
        $processor = $this->getProtectedProperty($this->command, 'processor');
        $this->assertEquals('Workspace\Packages\GetList', $processor);
    }

    public function testConfigureHasCorrectName()
    {
        $this->assertEquals('package:list', $this->command->getName());
    }

    public function testConfigureHasCorrectDescription()
    {
        $this->assertEquals('Get a list of packages in MODX', $this->command->getDescription());
    }

    public function testConfigureHasCorrectHeaders()
    {
        $headers = $this->getProtectedProperty($this->command, 'headers');
        $this->assertIsArray($headers);
        $this->assertContains('signature', $headers);
        $this->assertContains('name', $headers);
        $this->assertContains('version', $headers);
        $this->assertContains('release', $headers);
        $this->assertContains('installed', $headers);
        $this->assertContains('provider', $headers);
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
                        'signature' => 'package1-1.0.0-pl',
                        'name' => 'package1',
                        'version' => '1.0.0',
                        'release' => 'pl',
                        'installed' => '2023-01-01 12:00:00',
                        'provider' => 1
                    ],
                    [
                        'signature' => 'package2-2.0.0-pl',
                        'name' => 'package2',
                        'version' => '2.0.0',
                        'release' => 'pl',
                        'installed' => null,
                        'provider' => 1
                    ],
                ]
            ]));
        $processorResponse->method('isError')->willReturn(false);
        
        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->with('Workspace\Packages\GetList')
            ->willReturn($processorResponse);
        
        // Execute the command
        $this->commandTester->execute([
            'command' => $this->command->getName(),
        ]);
        
        // Verify the output contains package data
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('package1', $output);
        $this->assertStringContainsString('package2', $output);
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
                    [
                        'signature' => 'package1-1.0.0-pl',
                        'name' => 'package1',
                        'version' => '1.0.0',
                        'release' => 'pl',
                        'installed' => '2023-01-01 12:00:00',
                        'provider' => 1
                    ],
                ]
            ]));
        $processorResponse->method('isError')->willReturn(false);
        
        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->willReturn($processorResponse);
        
        // Execute the command with --json option
        $this->commandTester->execute([
            'command' => $this->command->getName(),
            '--json' => true,
        ]);
        
        // Verify the output is valid JSON
        $output = $this->commandTester->getDisplay();
        $decoded = json_decode($output, true);
        $this->assertNotNull($decoded);
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
                    [
                        'signature' => 'package1-1.0.0-pl',
                        'name' => 'package1',
                        'version' => '1.0.0',
                        'release' => 'pl',
                        'installed' => '2023-01-01 12:00:00',
                        'provider' => 1
                    ],
                ]
            ]));
        $processorResponse->method('isError')->willReturn(false);
        
        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->with(
                'Workspace\Packages\GetList',
                $this->callback(function($properties) {
                    return isset($properties['limit']) && $properties['limit'] === 10 &&
                           isset($properties['start']) && $properties['start'] === 20;
                }),
                $this->anything()
            )
            ->willReturn($processorResponse);
        
        // Execute the command with pagination options
        $this->commandTester->execute([
            'command' => $this->command->getName(),
            '--limit' => 10,
            '--start' => 20,
        ]);
        
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('package1', $output);
    }
}
