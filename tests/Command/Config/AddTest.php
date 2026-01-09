<?php namespace MODX\CLI\Tests\Command\Config;

use MODX\CLI\Command\Config\Add;
use MODX\CLI\Tests\Configuration\BaseTest;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Tester\CommandTester;

class AddTest extends BaseTest
{
    protected $command;
    protected $commandTester;
    protected $app;
    protected $instances;

    protected function setUp(): void
    {
        $this->instances = new FakeConfigStore();
        $this->app = $this->getMockBuilder(\MODX\CLI\Application::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->app->instances = $this->instances;
        $this->app->method('getCwd')->willReturn('/tmp/');
        $helperSet = new HelperSet([new QuestionHelper()]);
        $this->app->method('getHelperSet')->willReturn($helperSet);
        $this->app->method('getDefinition')->willReturn(new InputDefinition());

        // Create the command
        $this->command = new Add();
        $this->command->setApplication($this->app);
        $this->command->setHelperSet($helperSet);
        
        // Create a command tester
        $this->commandTester = new CommandTester($this->command);
    }

    public function testConfigureHasCorrectName()
    {
        $this->assertEquals('config:add', $this->command->getName());
    }

    public function testConfigureHasCorrectDescription()
    {
        $this->assertEquals('Add a MODX instance to the configuration', $this->command->getDescription());
    }

    public function testConfigureHasCorrectHelp()
    {
        $this->assertStringContainsString('adds a MODX instance to the configuration', $this->command->getHelp());
    }

    public function testConfigureHasNameArgument()
    {
        $definition = $this->command->getDefinition();
        $this->assertTrue($definition->hasArgument('name'));
        $this->assertTrue($definition->getArgument('name')->isRequired());
    }

    public function testConfigureHasBasePathOption()
    {
        $definition = $this->command->getDefinition();
        $this->assertTrue($definition->hasOption('base_path'));
        $this->assertTrue($definition->getOption('base_path')->isValueRequired());
    }

    public function testConfigureHasDefaultOption()
    {
        $definition = $this->command->getDefinition();
        $this->assertTrue($definition->hasOption('default'));
        $this->assertFalse($definition->getOption('default')->acceptValue());
    }

    public function testExecuteAddsInstance()
    {
        $dir = sys_get_temp_dir() . '/modx-cli-add-test';
        @mkdir($dir, 0777, true);
        @file_put_contents($dir . '/config.core.php', 'test');

        $this->commandTester->execute([
            'name' => 'site',
            '--base_path' => $dir
        ]);

        $instance = $this->instances->get('site');
        $this->assertEquals($dir . '/', $instance['base_path']);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString("Instance 'site' added", $output);

        @unlink($dir . '/config.core.php');
        @rmdir($dir);
    }

    public function testExecuteAddsInstanceWithJsonOutput()
    {
        $dir = sys_get_temp_dir() . '/modx-cli-add-json-test';
        @mkdir($dir, 0777, true);
        @file_put_contents($dir . '/config.core.php', 'test');

        $this->commandTester->execute([
            'name' => 'site',
            '--base_path' => $dir,
            '--json' => true
        ]);

        $decoded = json_decode($this->commandTester->getDisplay(), true);
        $this->assertTrue($decoded['success']);
        $this->assertEquals("Instance 'site' added", $decoded['message']);
        $this->assertEquals('site', $decoded['instance']['name']);
        $this->assertEquals($dir . '/', $decoded['instance']['base_path']);
        $this->assertFalse($decoded['instance']['is_default']);

        @unlink($dir . '/config.core.php');
        @rmdir($dir);
    }

    public function testExecuteAddsDefaultInstance()
    {
        $dir = sys_get_temp_dir() . '/modx-cli-add-default-test';
        @mkdir($dir, 0777, true);
        @file_put_contents($dir . '/config.core.php', 'test');

        $this->commandTester->execute([
            'name' => 'site',
            '--base_path' => $dir,
            '--default' => true
        ]);

        $this->assertEquals('site', $this->instances->get('__default__')['class']);
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString("Instance 'site' added and set as default", $output);

        @unlink($dir . '/config.core.php');
        @rmdir($dir);
    }

    public function testExecuteWithExistingInstanceAborts()
    {
        $this->instances->set('site', ['base_path' => '/old/path/']);

        $this->commandTester->setInputs(['no']);
        $this->commandTester->execute([
            'name' => 'site',
            '--base_path' => '/new/path/'
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Operation aborted', $output);
        $this->assertEquals('/old/path/', $this->instances->get('site')['base_path']);
    }
}
