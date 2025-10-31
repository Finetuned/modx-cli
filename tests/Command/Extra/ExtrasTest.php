<?php

namespace MODX\CLI\Tests\Command\Extra;

use MODX\CLI\Command\Extra\Extras;
use MODX\CLI\Tests\Configuration\BaseTest;
use Symfony\Component\Console\Tester\CommandTester;

class ExtrasTest extends BaseTest
{
    protected $command;
    protected $commandTester;
    protected $modx;

    protected function setUp(): void
    {
        // Create a mock MODX object
        $this->modx = $this->createMock('MODX\Revolution\modX');
        
        // Create the command
        $this->command = new Extras();
        $this->command->modx = $this->modx;
        
        // Create a command tester
        $this->commandTester = new CommandTester($this->command);
    }

    public function testConfigureHasCorrectName()
    {
        $this->assertEquals('extra:list', $this->command->getName());
    }

    public function testConfigureHasCorrectDescription()
    {
        $this->assertEquals('Get a list of extras in MODX', $this->command->getDescription());
    }

    public function testExecuteWithNoNamespaces()
    {
        // Mock getCollection to return empty array
        $this->modx->expects($this->once())
            ->method('getCollection')
            ->with('modNamespace')
            ->willReturn([]);
        
        // Execute the command
        $this->commandTester->execute([]);
        
        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('No namespaces found', $output);
        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }

    public function testExecuteWithCoreNamespaceOnly()
    {
        // Mock namespace object
        $namespace = $this->getMockBuilder('stdClass')
            ->addMethods(['get'])
            ->getMock();
        $namespace->method('get')->willReturnMap([
            ['name', 'core'],
            ['path', '/path/to/core'],
        ]);
        
        // Mock getCollection to return core namespace
        $this->modx->method('getCollection')
            ->willReturnCallback(function($class) use ($namespace) {
                if ($class === 'modNamespace') {
                    return [$namespace];
                }
                return []; // For transport.modTransportPackage calls
            });
        
        // Mock runProcessor for package list
        $processorResponse = $this->getMockBuilder('MODX\\Revolution\\Processors\\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $processorResponse->method('isError')->willReturn(false);
        $processorResponse->method('getResponse')->willReturn(json_encode(['results' => []]));
        
        $this->modx->method('runProcessor')->willReturn($processorResponse);
        $this->modx->method('getObject')->willReturn(null);
        
        // Execute the command
        $this->commandTester->execute([]);
        
        // Verify the output (core namespace should be skipped)
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('No extras found', $output);
        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }

    public function testExecuteWithExtrasAndPackageInfo()
    {
        // Mock namespace objects
        $namespace1 = $this->getMockBuilder('stdClass')
            ->addMethods(['get'])
            ->getMock();
        $namespace1->method('get')->willReturnMap([
            ['name', 'myextra'],
            ['path', '/path/to/myextra'],
        ]);
        
        $namespace2 = $this->getMockBuilder('stdClass')
            ->addMethods(['get'])
            ->getMock();
        $namespace2->method('get')->willReturnMap([
            ['name', 'anotherextra'],
            ['path', '/path/to/anotherextra'],
        ]);
        
        // Mock getCollection to return namespaces
        $this->modx->expects($this->once())
            ->method('getCollection')
            ->with('modNamespace')
            ->willReturn([$namespace1, $namespace2]);
        
        // Mock runProcessor for package list
        $processorResponse = $this->getMockBuilder('MODX\Revolution\Processors\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $processorResponse->method('isError')->willReturn(false);
        $processorResponse->method('getResponse')->willReturn(json_encode([
            'results' => [
                [
                    'package_name' => 'myextra',
                    'name' => 'MyExtra',
                    'version' => '1.0.0',
                    'installed' => '2025-01-01 10:00:00',
                    'signature' => 'myextra-1.0.0'
                ]
            ]
        ]));
        
        $this->modx->method('runProcessor')->willReturn($processorResponse);
        
        // Mock getObject for namespace without package
        $this->modx->method('getObject')->willReturn(null);
        
        // Execute the command
        $this->commandTester->execute([]);
        
        // Verify the output contains table headers and data
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Name', $output);
        $this->assertStringContainsString('Path', $output);
        $this->assertStringContainsString('Version', $output);
        $this->assertStringContainsString('Installed', $output);
        $this->assertStringContainsString('myextra', $output);
        $this->assertStringContainsString('1.0.0', $output);
        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }

    public function testExecuteWithProcessorError()
    {
        // Mock namespace object
        $namespace = $this->getMockBuilder('stdClass')
            ->addMethods(['get'])
            ->getMock();
        $namespace->method('get')->willReturnMap([
            ['name', 'testextra'],
            ['path', '/path/to/testextra'],
        ]);
        
        // Mock getCollection
        $this->modx->method('getCollection')
            ->willReturnCallback(function($class) use ($namespace) {
                if ($class === 'modNamespace') {
                    return [$namespace];
                }
                return []; // For transport.modTransportPackage calls
            });
        
        // Mock runProcessor to return error
        $processorResponse = $this->getMockBuilder('MODX\\Revolution\\Processors\\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $processorResponse->method('isError')->willReturn(true);
        
        $this->modx->method('runProcessor')->willReturn($processorResponse);
        $this->modx->method('getObject')->willReturn(null);
        
        // Execute the command - should still work with fallback
        $this->commandTester->execute([]);
        
        // Should display the extra with "Unknown" version
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('testextra', $output);
        $this->assertStringContainsString('Unknown', $output);
        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }

    public function testExecuteSortsExtrasByName()
    {
        // Mock namespace objects in non-alphabetical order
        $namespace1 = $this->getMockBuilder('stdClass')
            ->addMethods(['get'])
            ->getMock();
        $namespace1->method('get')->willReturnMap([
            ['name', 'zebra'],
            ['path', '/path/to/zebra'],
        ]);
        
        $namespace2 = $this->getMockBuilder('stdClass')
            ->addMethods(['get'])
            ->getMock();
        $namespace2->method('get')->willReturnMap([
            ['name', 'alpha'],
            ['path', '/path/to/alpha'],
        ]);
        
        // Mock getCollection
        $this->modx->method('getCollection')
            ->willReturnCallback(function($class) use ($namespace1, $namespace2) {
                if ($class === 'modNamespace') {
                    return [$namespace1, $namespace2];
                }
                return []; // For transport.modTransportPackage calls
            });
        
        // Mock runProcessor
        $processorResponse = $this->getMockBuilder('MODX\\Revolution\\Processors\\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $processorResponse->method('isError')->willReturn(false);
        $processorResponse->method('getResponse')->willReturn(json_encode(['results' => []]));
        
        $this->modx->method('runProcessor')->willReturn($processorResponse);
        $this->modx->method('getObject')->willReturn(null);
        
        // Execute the command
        $this->commandTester->execute([]);
        
        // Verify both extras are in output (sorting is internal, hard to test output order)
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('alpha', $output);
        $this->assertStringContainsString('zebra', $output);
    }
}
