<?php namespace MODX\CLI\Tests\Command\Package;

use MODX\CLI\Command\Package\Install;
use MODX\CLI\Tests\Configuration\BaseTest;
use MODX\CLI\Application;
use Symfony\Component\Console\Tester\CommandTester;

class InstallTest extends BaseTest
{
    protected $command;
    protected $commandTester;
    protected $modx;

    protected function setUp(): void
    {
        // Create a mock MODX object
        $this->modx = $this->createMock('MODX\Revolution\modX');
        
        // Create the command
        $this->command = new Install();
        $this->command->modx = $this->modx;
        
        // Create a command tester without using the Application class to avoid conflicts
        $this->commandTester = new CommandTester($this->command);
        
        // Set non-interactive mode to avoid confirmation prompts in tests
        $this->commandTester->setInputs(['yes']);
    }

    public function testConfigureHasCorrectProcessorPath()
    {
        $processor = $this->getProtectedProperty($this->command, 'processor');
        $this->assertEquals('Workspace\Packages\Install', $processor);
    }

    public function testConfigureHasCorrectName()
    {
        $this->assertEquals('package:install', $this->command->getName());
    }

    public function testConfigureHasCorrectDescription()
    {
        $this->assertEquals('Install a package in MODX', $this->command->getDescription());
    }

    public function testExecuteWithSuccessfulResponse()
    {
        // Mock package object
        $package = $this->getMockBuilder('stdClass')
            ->addMethods(['get'])
            ->getMock();
        $package->method('get')->willReturnMap([
            ['installed', null]
        ]);
        
        // Mock getObject to return package
        $this->modx->expects($this->once())
            ->method('getObject')
            ->with(\MODX\Revolution\Transport\modTransportPackage::class, ['signature' => 'package1-1.0.0-pl'], $this->anything())
            ->willReturn($package);
        
        // Mock the runProcessor method to return a successful response
        $processorResponse = $this->getMockBuilder('MODX\Revolution\Processors\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $processorResponse->method('getResponse')
            ->willReturn(json_encode([
                'success' => true
            ]));
        $processorResponse->method('isError')->willReturn(false);
        
        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->with(
                'Workspace\Packages\Install',
                $this->callback(function($properties) {
                    return isset($properties['signature']) && $properties['signature'] === 'package1-1.0.0-pl';
                }),
                $this->anything()
            )
            ->willReturn($processorResponse);
        
        // Execute the command with --force option to skip confirmation
        $this->commandTester->execute([
            'signature' => 'package1-1.0.0-pl',
            '--force' => true
        ]);
        
        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Package installed successfully', $output);
    }

    public function testExecuteWithNonExistentPackage()
    {
        // Mock getObject to return null (package doesn't exist)
        $this->modx->expects($this->once())
            ->method('getObject')
            ->with(\MODX\Revolution\Transport\modTransportPackage::class, ['signature' => 'nonexistent-1.0.0-pl'], $this->anything())
            ->willReturn(null);
        
        // runProcessor should not be called since the package doesn't exist
        $this->modx->expects($this->never())
            ->method('runProcessor');
        
        // Execute the command
        $this->commandTester->execute([
            'signature' => 'nonexistent-1.0.0-pl',
            '--force' => true
        ]);
        
        // Verify the output shows error message
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Package with signature \'nonexistent-1.0.0-pl\' not found', $output);
    }

    public function testExecuteWithAlreadyInstalledPackage()
    {
        // Mock package object that's already installed
        $package = $this->getMockBuilder('stdClass')
            ->addMethods(['get'])
            ->getMock();
        $package->method('get')->willReturnMap([
            ['installed', '2023-01-01 12:00:00']
        ]);
        
        // Mock getObject to return already installed package
        $this->modx->expects($this->once())
            ->method('getObject')
            ->with(\MODX\Revolution\Transport\modTransportPackage::class, ['signature' => 'package1-1.0.0-pl'], $this->anything())
            ->willReturn($package);
        
        // runProcessor should not be called since the package is already installed
        $this->modx->expects($this->never())
            ->method('runProcessor');
        
        // Execute the command
        $this->commandTester->execute([
            'signature' => 'package1-1.0.0-pl',
            '--force' => true
        ]);
        
        // Verify the output shows error message
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Package \'package1-1.0.0-pl\' is already installed', $output);
    }

    public function testExecuteWithFailedResponse()
    {
        // Mock package object
        $package = $this->getMockBuilder('stdClass')
            ->addMethods(['get'])
            ->getMock();
        $package->method('get')->willReturnMap([
            ['installed', null]
        ]);
        
        // Mock getObject to return package
        $this->modx->expects($this->once())
            ->method('getObject')
            ->with(\MODX\Revolution\Transport\modTransportPackage::class, ['signature' => 'package1-1.0.0-pl'], $this->anything())
            ->willReturn($package);
        
        // Mock the runProcessor method to return a failed response
        $processorResponse = $this->getMockBuilder('MODX\Revolution\Processors\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $processorResponse->method('getResponse')
            ->willReturn(json_encode([
                'success' => false,
                'message' => 'Error installing package'
            ]));
        $processorResponse->method('isError')->willReturn(true);
        
        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->willReturn($processorResponse);
        
        // Execute the command
        $this->commandTester->execute([
            'signature' => 'package1-1.0.0-pl',
            '--force' => true
        ]);
        
        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Failed to install package', $output);
        $this->assertStringContainsString('Error installing package', $output);
    }
}
