<?php

namespace MODX\CLI\Tests\Command\Config;

use MODX\CLI\Command\Config\RmDefault;
use MODX\CLI\Tests\Configuration\BaseTest;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Tester\CommandTester;

class RmDefaultTest extends BaseTest
{
    private function makeCommandTester(FakeConfigStore $instances): array
    {
        $app = $this->getMockBuilder(\MODX\CLI\Application::class)
            ->disableOriginalConstructor()
            ->getMock();
        $app->instances = $instances;
        $helperSet = new HelperSet([new QuestionHelper()]);
        $app->method('getHelperSet')->willReturn($helperSet);
        $app->method('getDefinition')->willReturn(new InputDefinition());

        $command = new RmDefault();
        $command->setApplication($app);

        return [new CommandTester($command), $instances];
    }

    public function testExecuteWithNoDefault()
    {
        [$tester] = $this->makeCommandTester(new FakeConfigStore([]));
        $tester->execute([]);

        $output = $tester->getDisplay();
        $this->assertStringContainsString('No default instance set', $output);
    }

    public function testExecuteRemovesDefaultInstance()
    {
        [$tester, $instances] = $this->makeCommandTester(new FakeConfigStore([
            '__default__' => ['class' => 'site'],
        ]));

        $tester->execute([]);

        $this->assertNull($instances->get('__default__'));
        $output = $tester->getDisplay();
        $this->assertStringContainsString("Default instance 'site' removed", $output);
    }
}
