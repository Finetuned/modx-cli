<?php

namespace MODX\CLI\Tests\Integration\Commands\Config;

/**
 * Integration test for config:exclude-command command
 */
class ExcludeCommandTest extends ConfigTestCase
{
    public function testExcludeCommandWritesExcludedConfig()
    {
        $commandClass = 'MODX\\CLI\\Command\\FakeCommand';

        $process = $this->runConfigCommand([
            'config:exclude-command',
            $commandClass,
            '--json',
        ]);

        $this->assertSame(0, $process->getExitCode());
        $data = json_decode($process->getOutput(), true);
        $this->assertTrue($data['success']);
        $this->assertTrue($data['excluded']);

        $excluded = $this->readJsonFile($this->excludedFile);
        $this->assertArrayHasKey($commandClass, $excluded);
    }
}
