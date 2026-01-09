<?php

namespace MODX\CLI\Tests\Command;

use MODX\CLI\Command\Install;
use MODX\CLI\Tests\Configuration\BaseTest;
use Symfony\Component\Console\Tester\CommandTester;

class InstallTest extends BaseTest
{
    public function testConfigureHasCorrectName()
    {
        $command = new Install();
        $this->assertEquals('install', $command->getName());
    }

    public function testExecuteOutputsDisabledMessage()
    {
        $command = new Install();
        $command->setApplication($this->makeAppMock());
        $tester = new CommandTester($command);
        $tester->execute([]);

        $output = $tester->getDisplay();
        $this->assertStringContainsString('install command is disabled', $output);
        $this->assertEquals(1, $tester->getStatusCode());
    }

    private function makeAppMock()
    {
        $app = $this->getMockBuilder(\MODX\CLI\Application::class)
            ->disableOriginalConstructor()
            ->getMock();
        $app->method('getHelperSet')->willReturn(new \Symfony\Component\Console\Helper\HelperSet());
        $app->method('getDefinition')->willReturn(new \Symfony\Component\Console\Input\InputDefinition());

        return $app;
    }
}
