<?php

namespace MODX\CLI\Tests\Command\Config;

use MODX\CLI\Command\Config\SetDefault;
use MODX\CLI\Tests\Configuration\BaseTest;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Tester\CommandTester;

class SetDefaultTest extends BaseTest
{
    protected function makeCommandTester(FakeConfigStore $instances): CommandTester
    {
        $app = $this->getMockBuilder(\MODX\CLI\Application::class)
            ->disableOriginalConstructor()
            ->getMock();
        $app->instances = $instances;
        $helperSet = new HelperSet([new QuestionHelper()]);
        $app->method('getHelperSet')->willReturn($helperSet);
        $app->method('getDefinition')->willReturn(new InputDefinition());

        $command = new SetDefault();
        $command->setApplication($app);

        return new CommandTester($command);
    }

    public function testConfigureHasCorrectName()
    {
        $command = new SetDefault();
        $this->assertEquals('config:set-default', $command->getName());
    }

    public function testExecuteWithMissingInstance()
    {
        $tester = $this->makeCommandTester(new FakeConfigStore([]));
        $tester->execute([
            'name' => 'missing'
        ]);

        $output = $tester->getDisplay();
        $this->assertStringContainsString("Instance 'missing' does not exist", $output);
        $this->assertEquals(1, $tester->getStatusCode());
    }

    public function testExecuteSetsDefaultInstance()
    {
        $instances = new FakeConfigStore([
            'site' => ['base_path' => '/path/site/'],
        ]);
        $tester = $this->makeCommandTester($instances);

        $tester->execute([
            'name' => 'site'
        ]);

        $this->assertEquals('site', $instances->get('__default__')['class']);
        $output = $tester->getDisplay();
        $this->assertStringContainsString("Instance 'site' set as default", $output);
    }

    public function testExecuteSetsDefaultInstanceWithJsonOutput()
    {
        $instances = new FakeConfigStore([
            'site' => ['base_path' => '/path/site/'],
        ]);
        $tester = $this->makeCommandTester($instances);

        $tester->execute([
            'name' => 'site',
            '--json' => true
        ]);

        $decoded = json_decode($tester->getDisplay(), true);
        $this->assertTrue($decoded['success']);
        $this->assertEquals("Instance 'site' set as default", $decoded['message']);
        $this->assertEquals('site', $decoded['default']['name']);
    }
}
