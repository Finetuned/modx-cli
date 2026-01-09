<?php

namespace MODX\CLI\Tests\Command\Config;

use MODX\CLI\Command\Config\GetExcludeCommand;
use MODX\CLI\Tests\Configuration\BaseTest;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Tester\CommandTester;

class GetExcludeCommandTest extends BaseTest
{
    private function makeCommandTester(FakeExcludedCommands $excluded): CommandTester
    {
        $app = $this->getMockBuilder(\MODX\CLI\Application::class)
            ->disableOriginalConstructor()
            ->getMock();
        $app->excludedCommands = $excluded;
        $helperSet = new HelperSet([new QuestionHelper()]);
        $app->method('getHelperSet')->willReturn($helperSet);
        $app->method('getDefinition')->willReturn(new InputDefinition());

        $command = new GetExcludeCommand();
        $command->setApplication($app);

        return new CommandTester($command);
    }

    public function testExecuteWithNoExcludedCommands()
    {
        $tester = $this->makeCommandTester(new FakeExcludedCommands([]));
        $tester->execute([]);

        $output = $tester->getDisplay();
        $this->assertStringContainsString('No commands are excluded', $output);
    }

    public function testExecuteWithExcludedCommands()
    {
        $tester = $this->makeCommandTester(new FakeExcludedCommands([
            'Some\\Command\\Class',
            'Other\\Command\\Class',
        ]));
        $tester->execute([]);

        $output = $tester->getDisplay();
        $this->assertStringContainsString('Some\\Command\\Class', $output);
        $this->assertStringContainsString('Other\\Command\\Class', $output);
    }

    public function testExecuteWithExcludedCommandsJsonOutput()
    {
        $tester = $this->makeCommandTester(new FakeExcludedCommands([
            'Some\\Command\\Class',
            'Other\\Command\\Class',
        ]));

        $tester->execute([
            '--json' => true
        ]);

        $decoded = json_decode($tester->getDisplay(), true);
        $this->assertEquals(2, $decoded['total']);
        $this->assertEquals(['Some\\Command\\Class', 'Other\\Command\\Class'], $decoded['results']);
    }
}
