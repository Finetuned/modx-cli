<?php

namespace MODX\CLI\Tests\Command\Config;

use MODX\CLI\Command\Config\GetDefault;
use MODX\CLI\Tests\Configuration\BaseTest;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Tester\CommandTester;

class GetDefaultTest extends BaseTest
{
    public function testExecuteWithNoDefault()
    {
        [$commandTester] = $this->makeCommandTester(new FakeConfigStore([]));
        $commandTester->execute([]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('No default instance set', $output);
    }

    public function testExecuteWithDefaultMissingName()
    {
        [$commandTester] = $this->makeCommandTester(new FakeConfigStore([
            '__default__' => ['class' => null],
        ]));

        $commandTester->execute([]);
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Default instance is set but has no name', $output);
    }

    public function testExecuteWithMissingDefaultInstance()
    {
        [$commandTester] = $this->makeCommandTester(new FakeConfigStore([
            '__default__' => ['class' => 'missing'],
        ]));

        $commandTester->execute([]);
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString("Default instance 'missing' does not exist", $output);
    }

    public function testExecuteWithDefaultInstance()
    {
        [$commandTester] = $this->makeCommandTester(new FakeConfigStore([
            '__default__' => ['class' => 'site'],
            'site' => ['base_path' => '/path/site/'],
        ]));

        $commandTester->execute([]);
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Default instance: site', $output);
        $this->assertStringContainsString('Base path: /path/site/', $output);
    }

    public function testExecuteWithDefaultInstanceJsonOutput()
    {
        [$commandTester] = $this->makeCommandTester(new FakeConfigStore([
            '__default__' => ['class' => 'site'],
            'site' => ['base_path' => '/path/site/'],
        ]));

        $commandTester->execute([
            '--json' => true
        ]);

        $decoded = json_decode($commandTester->getDisplay(), true);
        $this->assertTrue($decoded['success']);
        $this->assertEquals('site', $decoded['default']['name']);
        $this->assertEquals('/path/site/', $decoded['default']['base_path']);
    }

    private function makeCommandTester(FakeConfigStore $instances): array
    {
        $app = $this->getMockBuilder(\MODX\CLI\Application::class)
            ->disableOriginalConstructor()
            ->getMock();
        $app->instances = $instances;
        $helperSet = new HelperSet([new QuestionHelper()]);
        $app->method('getHelperSet')->willReturn($helperSet);
        $app->method('getDefinition')->willReturn(new InputDefinition());

        $command = new GetDefault();
        $command->setApplication($app);

        return [new CommandTester($command), $command];
    }
}
