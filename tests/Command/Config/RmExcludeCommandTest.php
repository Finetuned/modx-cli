<?php

namespace MODX\CLI\Tests\Command\Config;

use MODX\CLI\Command\Config\RmExcludeCommand;
use MODX\CLI\Tests\Configuration\BaseTest;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Tester\CommandTester;

class RmExcludeCommandTest extends BaseTest
{
    private function makeCommandTester(FakeExcludedCommands $excluded): array
    {
        $app = $this->getMockBuilder(\MODX\CLI\Application::class)
            ->disableOriginalConstructor()
            ->getMock();
        $app->excludedCommands = $excluded;
        $helperSet = new HelperSet([new QuestionHelper()]);
        $app->method('getHelperSet')->willReturn($helperSet);
        $app->method('getDefinition')->willReturn(new InputDefinition());

        $command = new RmExcludeCommand();
        $command->setApplication($app);

        return [new CommandTester($command), $excluded];
    }

    public function testExecuteWhenNotExcluded()
    {
        [$tester] = $this->makeCommandTester(new FakeExcludedCommands([]));
        $tester->execute([
            'class' => 'Some\\Command\\Class'
        ]);

        $output = $tester->getDisplay();
        $this->assertStringContainsString("Command 'Some\\Command\\Class' is not excluded", $output);
    }

    public function testExecuteRemovesExcludedCommand()
    {
        [$tester, $excluded] = $this->makeCommandTester(new FakeExcludedCommands([
            'Some\\Command\\Class',
        ]));

        $tester->execute([
            'class' => 'Some\\Command\\Class'
        ]);

        $this->assertNull($excluded->get('Some\\Command\\Class'));
        $output = $tester->getDisplay();
        $this->assertStringContainsString("Command 'Some\\Command\\Class' removed from excluded commands", $output);
    }
}
