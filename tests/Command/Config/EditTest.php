<?php

namespace MODX\CLI\Tests\Command\Config;

use MODX\CLI\Command\Config\Edit;
use MODX\CLI\Tests\Configuration\BaseTest;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Tester\CommandTester;

class EditTest extends BaseTest
{
    protected $command;
    protected $commandTester;
    protected $app;
    protected $instances;

    protected function setUp(): void
    {
        $this->instances = new FakeConfigStore([
            'site' => ['base_path' => '/old/path/'],
        ]);

        $this->app = $this->getMockBuilder(\MODX\CLI\Application::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->app->instances = $this->instances;
        $helperSet = new HelperSet([new QuestionHelper()]);
        $this->app->method('getHelperSet')->willReturn($helperSet);
        $this->app->method('getDefinition')->willReturn(new InputDefinition());

        $this->command = new Edit();
        $this->command->setApplication($this->app);
        $this->command->setHelperSet($helperSet);

        $this->commandTester = new CommandTester($this->command);
    }

    public function testConfigureHasCorrectName()
    {
        $this->assertEquals('config:edit', $this->command->getName());
    }

    public function testConfigureHasCorrectDescription()
    {
        $this->assertEquals('Edit a MODX instance in the configuration', $this->command->getDescription());
    }

    public function testConfigureHasArgumentAndOptions()
    {
        $definition = $this->command->getDefinition();
        $this->assertTrue($definition->hasArgument('name'));
        $this->assertTrue($definition->getArgument('name')->isRequired());
        $this->assertTrue($definition->hasOption('base_path'));
        $this->assertTrue($definition->getOption('base_path')->isValueRequired());
        $this->assertTrue($definition->hasOption('default'));
        $this->assertFalse($definition->getOption('default')->acceptValue());
    }

    public function testExecuteWithMissingInstance()
    {
        $this->commandTester->execute([
            'name' => 'missing'
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString("Instance 'missing' does not exist", $output);
        $this->assertEquals(1, $this->commandTester->getStatusCode());
    }

    public function testExecuteUpdatesBasePathAndDefault()
    {
        $dir = sys_get_temp_dir() . '/modx-cli-edit-test';
        @mkdir($dir, 0777, true);
        @file_put_contents($dir . '/config.core.php', 'test');

        $this->commandTester->execute([
            'name' => 'site',
            '--base_path' => $dir,
            '--default' => true
        ]);

        $updated = $this->instances->get('site');
        $this->assertEquals($dir . '/', $updated['base_path']);
        $this->assertEquals('site', $this->instances->get('__default__')['class']);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString("Instance 'site' updated and set as default", $output);

        @unlink($dir . '/config.core.php');
        @rmdir($dir);
    }

    public function testExecuteUpdatesBasePathWithJsonOutput()
    {
        $dir = sys_get_temp_dir() . '/modx-cli-edit-json-test';
        @mkdir($dir, 0777, true);
        @file_put_contents($dir . '/config.core.php', 'test');

        $this->commandTester->execute([
            'name' => 'site',
            '--base_path' => $dir,
            '--json' => true
        ]);

        $decoded = json_decode($this->commandTester->getDisplay(), true);
        $this->assertTrue($decoded['success']);
        $this->assertEquals("Instance 'site' updated", $decoded['message']);
        $this->assertEquals('site', $decoded['instance']['name']);
        $this->assertEquals($dir . '/', $decoded['instance']['base_path']);
        $this->assertFalse($decoded['instance']['is_default']);

        @unlink($dir . '/config.core.php');
        @rmdir($dir);
    }

    public function testExecuteWithMissingConfigCoreAborts()
    {
        $this->commandTester->setInputs(['no']);
        $this->commandTester->execute([
            'name' => 'site',
            '--base_path' => sys_get_temp_dir() . '/modx-cli-missing-config'
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Operation aborted', $output);
        $this->assertEquals('/old/path/', $this->instances->get('site')['base_path']);
    }
}
