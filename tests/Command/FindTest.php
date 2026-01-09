<?php

namespace MODX\CLI\Tests\Command;

use MODX\CLI\Command\Find;
use MODX\CLI\Tests\Configuration\BaseTest;
use Symfony\Component\Console\Output\BufferedOutput;

class FindTest extends BaseTest
{
    public function testConfigureHasCorrectName()
    {
        $command = new Find();
        $this->assertEquals('find', $command->getName());
    }

    public function testBeforeRunFailsForOldModxVersion()
    {
        $command = new Find();
        $modx = $this->createMock('MODX\Revolution\modX');
        $modx->method('getVersionData')
            ->willReturn(['full_version' => '2.2.0']);
        $command->modx = $modx;

        $output = new BufferedOutput();
        $this->setCommandOutput($command, $output);

        $method = new \ReflectionMethod($command, 'beforeRun');
        $method->setAccessible(true);
        $properties = [];
        $options = [];
        $result = $method->invoke($command, $properties, $options);

        $this->assertFalse($result);
        $this->assertStringContainsString('does not support that search function', $output->fetch());
    }

    public function testBeforeRunAllowsSupportedVersion()
    {
        $command = new Find();
        $modx = $this->createMock('MODX\Revolution\modX');
        $modx->method('getVersionData')
            ->willReturn(['full_version' => '2.3.0']);
        $command->modx = $modx;

        $method = new \ReflectionMethod($command, 'beforeRun');
        $method->setAccessible(true);
        $properties = [];
        $options = [];
        $result = $method->invoke($command, $properties, $options);

        $this->assertNotFalse($result);
    }

    private function setCommandOutput($command, BufferedOutput $output): void
    {
        $reflection = new \ReflectionClass($command);
        $prop = $reflection->getProperty('output');
        $prop->setAccessible(true);
        $prop->setValue($command, $output);
    }
}
