<?php namespace MODX\CLI\Tests\Command\Ns;

use MODX\CLI\Command\Ns\Remove;
use MODX\CLI\Tests\Configuration\BaseTest;
use Symfony\Component\Console\Tester\CommandTester;

class RemoveTest extends BaseTest
{
    protected $command;
    protected $commandTester;
    protected $modx;

    protected function setUp(): void
    {
        // Create a mock MODX object
        $this->modx = $this->createMock('MODX\Revolution\modX');
        
        // Create the command
        $this->command = new Remove();
        $this->command->modx = $this->modx;
        
        // Create a command tester
        $this->commandTester = new CommandTester($this->command);
    }

    public function testConfigureHasCorrectProcessorPath()
    {
        $processor = $this->getProtectedProperty($this->command, 'processor');
        $this->assertEquals('Workspace\PackageNamespace\Remove', $processor);
    }

    public function testConfigureHasCorrectName()
    {
        $this->assertEquals('ns:remove', $this->command->getName());
    }

    public function testConfigureHasCorrectDescription()
    {
        $this->assertEquals('Remove a namespace in MODX', $this->command->getDescription());
    }

    public function testExecuteWithSuccessfulRemove()
    {
        // Mock existing namespace object
        $existingNamespace = $this->getMockBuilder('stdClass')
            ->addMethods(['get'])
            ->getMock();
        $existingNamespace->method('get')->willReturn('testnamespace');
        
        // Mock getObject to return existing namespace
        $this->modx->expects($this->once())
            ->method('getObject')
            ->with(\MODX\Revolution\modNamespace::class, ['name' => 'testnamespace'], $this->anything())
            ->willReturn($existingNamespace);
        
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
                'Workspace\PackageNamespace\Remove',
                $this->callback(function($properties) {
                    return isset($properties['name']) && $properties['name'] === 'testnamespace';
                }),
                $this->anything()
            )
            ->willReturn($processorResponse);
        
        // Execute the command with --force to skip confirmation
        $this->commandTester->execute([
            'name' => 'testnamespace',
            '--force' => true
        ]);
        
        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Namespace removed successfully', $output);
    }

    public function testExecuteWithNonExistentNamespace()
    {
        // Mock getObject to return null (namespace doesn't exist)
        $this->modx->expects($this->once())
            ->method('getObject')
            ->with(\MODX\Revolution\modNamespace::class, ['name' => 'nonexistent'], $this->anything())
            ->willReturn(null);
        
        // runProcessor should not be called since the namespace doesn't exist
        $this->modx->expects($this->never())
            ->method('runProcessor');
        
        // Execute the command
        $this->commandTester->execute([
            'name' => 'nonexistent',
            '--force' => true
        ]);
        
        // Verify the output shows error message
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString("Namespace 'nonexistent' not found", $output);
    }

    public function testExecuteWithFailedResponse()
    {
        // Mock existing namespace object
        $existingNamespace = $this->getMockBuilder('stdClass')
            ->addMethods(['get'])
            ->getMock();
        $existingNamespace->method('get')->willReturn('protected');
        
        // Mock getObject to return existing namespace
        $this->modx->expects($this->once())
            ->method('getObject')
            ->with(\MODX\Revolution\modNamespace::class, ['name' => 'protected'], $this->anything())
            ->willReturn($existingNamespace);
        
        // Mock the runProcessor method to return a failed response
        $processorResponse = $this->getMockBuilder('MODX\Revolution\Processors\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        
        $processorResponse->method('getResponse')
            ->willReturn(json_encode([
                'success' => false,
                'message' => 'Cannot remove core namespace'
            ]));
        $processorResponse->method('isError')->willReturn(true);
        
        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->willReturn($processorResponse);
        
        // Execute the command
        $this->commandTester->execute([
            'name' => 'protected',
            '--force' => true
        ]);
        
        // Verify the output shows error
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Failed to remove namespace', $output);
        $this->assertStringContainsString('Cannot remove core namespace', $output);
    }

    public function testExecuteWithForceOption()
    {
        // Mock existing namespace object
        $existingNamespace = $this->getMockBuilder('stdClass')
            ->addMethods(['get'])
            ->getMock();
        $existingNamespace->method('get')->willReturn('testns');
        
        // Mock getObject to return existing namespace
        $this->modx->expects($this->once())
            ->method('getObject')
            ->willReturn($existingNamespace);
        
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
            ->willReturn($processorResponse);
        
        // Execute the command with --force flag
        $this->commandTester->execute([
            'name' => 'testns',
            '--force' => true
        ]);
        
        // Verify the command executed without asking for confirmation
        $output = $this->commandTester->getDisplay();
        $this->assertStringNotContainsString('Are you sure', $output);
        $this->assertStringContainsString('Namespace removed successfully', $output);
    }
}
