<?php

namespace MODX\CLI\Tests\Command\Config;

use MODX\CLI\Command\Config\WipeExcludeCommand;
use MODX\CLI\Tests\Configuration\BaseTest;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Tester\CommandTester;

class WipeExcludeCommandTest extends BaseTest
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

        $command = new WipeExcludeCommand();
        $command->setApplication($app);
        $command->setHelperSet($helperSet);

        return [new CommandTester($command), $excluded];
    }

    public function testExecuteWithNoExcludedCommands()
    {
        [$tester] = $this->makeCommandTester(new FakeExcludedCommands([]));
        $tester->execute([]);

        $output = $tester->getDisplay();
        $this->assertStringContainsString('No commands are excluded', $output);
    }

    public function testExecuteAbortsOnConfirmation()
    {
        [$tester, $excluded] = $this->makeCommandTester(new FakeExcludedCommands([
            'Some\\Command\\Class',
        ]));
        $tester->setInputs(['no']);

        $tester->execute([]);

        $output = $tester->getDisplay();
        $this->assertStringContainsString('Operation aborted', $output);
        $this->assertTrue($excluded->get('Some\\Command\\Class'));
    }

    public function testExecuteWipesExcludedCommands()
    {
        [$tester, $excluded] = $this->makeCommandTester(new FakeExcludedCommands([
            'Some\\Command\\Class',
            'Other\\Command\\Class',
        ]));
        $tester->setInputs(['yes']);

        $tester->execute([]);

        $this->assertEmpty($excluded->getAll());
        $output = $tester->getDisplay();
        $this->assertStringContainsString('All excluded commands wiped', $output);
    }

    public function testExecuteWipesExcludedCommandsWithJsonOutput()
    {
        [$tester, $excluded] = $this->makeCommandTester(new FakeExcludedCommands([]));
        $tester->execute([
            '--json' => true
        ]);

        $this->assertEmpty($excluded->getAll());
        $decoded = json_decode($tester->getDisplay(), true);
        $this->assertTrue($decoded['success']);
        $this->assertEquals('No commands are excluded', $decoded['message']);
        $this->assertFalse($decoded['wiped']);
        $this->assertEquals(0, $decoded['total']);
    }
}
