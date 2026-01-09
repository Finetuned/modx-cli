<?php

namespace MODX\CLI\Tests\Command\Config;

use MODX\CLI\Command\Config\ExcludeCommand;
use MODX\CLI\Tests\Configuration\BaseTest;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Tester\CommandTester;

class ExcludeCommandTest extends BaseTest
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

        $command = new ExcludeCommand();
        $command->setApplication($app);

        return [new CommandTester($command), $excluded];
    }

    public function testExecuteWhenAlreadyExcluded()
    {
        [$tester] = $this->makeCommandTester(new FakeExcludedCommands([
            'Some\\Command\\Class',
        ]));

        $tester->execute([
            'class' => 'Some\\Command\\Class'
        ]);

        $output = $tester->getDisplay();
        $this->assertStringContainsString("Command 'Some\\Command\\Class' is already excluded", $output);
    }

    public function testExecuteAddsExcludedCommand()
    {
        [$tester, $excluded] = $this->makeCommandTester(new FakeExcludedCommands([]));

        $tester->execute([
            'class' => 'Some\\Command\\Class'
        ]);

        $this->assertTrue($excluded->get('Some\\Command\\Class'));
        $output = $tester->getDisplay();
        $this->assertStringContainsString("Command 'Some\\Command\\Class' excluded", $output);
    }

    public function testExecuteAddsExcludedCommandWithJsonOutput()
    {
        [$tester, $excluded] = $this->makeCommandTester(new FakeExcludedCommands([]));

        $tester->execute([
            'class' => 'Some\\Command\\Class',
            '--json' => true
        ]);

        $this->assertTrue($excluded->get('Some\\Command\\Class'));
        $decoded = json_decode($tester->getDisplay(), true);
        $this->assertTrue($decoded['success']);
        $this->assertEquals("Command 'Some\\Command\\Class' excluded", $decoded['message']);
        $this->assertEquals('Some\\Command\\Class', $decoded['command']);
        $this->assertTrue($decoded['excluded']);
    }
}
