<?php namespace MODX\CLI\Tests\Command\Category;

use MODX\CLI\Command\Category\Remove;
//use PHPUnit\Framework\TestCase;
use MODX\CLI\Tests\Configuration\BaseTest;
use MODX\CLI\Application;
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
        $application = new Application();
        $application->add($this->command);
        $this->commandTester = new CommandTester($this->command);
    }

    public function testConfigureHasCorrectProcessorPath()
    {
        $processor = $this->getProtectedProperty($this->command, 'processor');
        $this->assertEquals('element/category/remove', $processor);
    }

    public function testConfigureHasCorrectName()
    {
        $this->assertEquals('category:remove', $this->command->getName());
    }

    public function testConfigureHasCorrectDescription()
    {
        $this->assertEquals('Remove a MODX category', $this->command->getDescription());
    }

    public function testExecuteWithSuccessfulResponse()
    {
        // Mock the category object
        $category = $this->getMockBuilder('MODX\Revolution\modCategory')
            ->disableOriginalConstructor()
            ->getMock();
        $category->method('get')
            ->with('category')
            ->willReturn('Test Category');
        
        // Mock the getObject method
        $this->modx->method('getObject')
            ->with('modCategory', '123')
            ->willReturn($category);
        
        // We'll skip the confirmation by using the force option
        
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
                'element/category/remove',
                $this->callback(function($properties) {
                    return isset($properties['id']) && $properties['id'] === '123';
                }),
                $this->anything()
            )
            ->willReturn($processorResponse);
        
        // Execute the command with force option
        $this->commandTester->execute([
            'command' => 'category:remove',
            'id' => '123',
            '--force' => true
        ]);
        
        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Category removed successfully', $output);
    }

    public function testExecuteWithForceOption()
    {
        // Mock the category object
        $category = $this->getMockBuilder('MODX\Revolution\modCategory')
            ->disableOriginalConstructor()
            ->getMock();
        $category->method('get')
            ->with('category')
            ->willReturn('Test Category');
        
        // Mock the getObject method
        $this->modx->method('getObject')
            ->with('modCategory', '123')
            ->willReturn($category);
        
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
        
        // Execute the command with force option
        $this->commandTester->execute([
            'command' => $this->command->getName(),
            'id' => '123',
            '--force' => true
        ]);
        
        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Category removed successfully', $output);
    }

    public function testExecuteWithCategoryNotFound()
    {
        // Mock the getObject method to return null (category not found)
        $this->modx->method('getObject')
            ->with('modCategory', '999')
            ->willReturn(null);
        
        // Execute the command
        $this->commandTester->execute([
            'command' => $this->command->getName(),
            'id' => '999'
        ]);
        
        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Category with ID 999 not found', $output);
    }

    public function testExecuteWithFailedResponse()
    {
        // Mock the category object
        $category = $this->getMockBuilder('MODX\Revolution\modCategory')
            ->disableOriginalConstructor()
            ->getMock();
        $category->method('get')
            ->with('category')
            ->willReturn('Test Category');
        
        // Mock the getObject method
        $this->modx->method('getObject')
            ->with('modCategory', '123')
            ->willReturn($category);
        
        // We'll skip the confirmation by using the force option
        
        // Mock the runProcessor method to return a failed response
        $processorResponse = $this->getMockBuilder('MODX\Revolution\Processors\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $processorResponse->method('getResponse')
            ->willReturn(json_encode([
                'success' => false,
                'message' => 'Error removing category'
            ]));
        $processorResponse->method('isError')->willReturn(true);
        
        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->willReturn($processorResponse);
        
        // Execute the command with force option
        $this->commandTester->execute([
            'command' => 'category:remove',
            '--force' => true,
            'id' => '123'
        ]);
        
        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Failed to remove category', $output);
        $this->assertStringContainsString('Error removing category', $output);
    }
}
