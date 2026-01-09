<?php

namespace MODX\CLI\Tests\Command;

use MODX\CLI\Command\Version;
use MODX\CLI\Tests\Configuration\BaseTest;
use Symfony\Component\Console\Tester\CommandTester;

class VersionTest extends BaseTest
{
    public function testExecuteOutputsCliAndModxVersions()
    {
        $command = new Version();

        $modx = $this->createMock('MODX\Revolution\modX');
        $modx->method('getVersionData')
            ->willReturn(['full_version' => '3.0.0']);

        $app = $this->getMockBuilder(\MODX\CLI\Application::class)
            ->disableOriginalConstructor()
            ->getMock();
        $app->method('getVersion')->willReturn('1.2.3');
        $app->method('getMODX')->willReturn($modx);
        $app->method('getHelperSet')->willReturn(new \Symfony\Component\Console\Helper\HelperSet());
        $app->method('getDefinition')->willReturn(new \Symfony\Component\Console\Input\InputDefinition());

        $command->setApplication($app);

        $tester = new CommandTester($command);
        $tester->execute([]);

        $output = $tester->getDisplay();
        $this->assertStringContainsString('MODX CLI version 1.2.3', $output);
        $this->assertStringContainsString('MODX version 3.0.0', $output);
    }

    public function testExecuteOutputsJson()
    {
        $command = new Version();

        $modx = $this->createMock('MODX\Revolution\modX');
        $modx->method('getVersionData')
            ->willReturn(['full_version' => '3.0.0']);

        $app = $this->getMockBuilder(\MODX\CLI\Application::class)
            ->disableOriginalConstructor()
            ->getMock();
        $app->method('getVersion')->willReturn('1.2.3');
        $app->method('getMODX')->willReturn($modx);
        $app->method('getHelperSet')->willReturn(new \Symfony\Component\Console\Helper\HelperSet());
        $app->method('getDefinition')->willReturn(new \Symfony\Component\Console\Input\InputDefinition());

        $command->setApplication($app);

        $tester = new CommandTester($command);
        $tester->execute(['--json' => true]);

        $decoded = json_decode($tester->getDisplay(), true);
        $this->assertTrue($decoded['success']);
        $this->assertEquals('1.2.3', $decoded['cli_version']);
        $this->assertEquals('3.0.0', $decoded['modx_version']);
    }
}
