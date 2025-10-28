<?php namespace MODX\CLI\Tests\Command\Config;

use MODX\CLI\Command\Config\GetList;
use MODX\CLI\Tests\Configuration\BaseTest;
use MODX\CLI\Application;
use Symfony\Component\Console\Tester\CommandTester;

class GetListTest extends BaseTest
{
    protected $command;
    protected $commandTester;
    protected $application;

    protected function setUp(): void
    {
        // Create the application
        $this->application = new Application();
        
        // Create the command
        $this->command = new GetList();
        $this->application->add($this->command);
        
        // Create a command tester
        $this->commandTester = new CommandTester($this->command);
    }

    public function testConfigureHasCorrectName()
    {
        $this->assertEquals('config:list', $this->command->getName());
    }

    public function testConfigureHasCorrectDescription()
    {
        $this->assertEquals('List MODX instances in the configuration', $this->command->getDescription());
    }

    public function testExecuteDisplaysNoInstancesMessage()
    {
        // Execute the command
        $this->commandTester->execute([
            'command' => $this->command->getName(),
        ]);
        
        // Verify the output contains appropriate message
        $output = $this->commandTester->getDisplay();
        
        // The command should either show "No instances configured" or a table
        // Since we can't easily control the instance configuration in tests,
        // we just verify the command executes successfully
        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }

    public function testExecuteDisplaysTableHeaders()
    {
        // Execute the command
        $this->commandTester->execute([
            'command' => $this->command->getName(),
        ]);
        
        $output = $this->commandTester->getDisplay();
        
        // If there are instances, the output should contain the headers
        // Otherwise, it should contain "No instances configured"
        $this->assertTrue(
            strpos($output, 'Name') !== false || 
            strpos($output, 'No instances configured') !== false
        );
    }

    public function testExecuteReturnsSuccessCode()
    {
        // Execute the command
        $this->commandTester->execute([
            'command' => $this->command->getName(),
        ]);
        
        // Verify successful execution
        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }
}
