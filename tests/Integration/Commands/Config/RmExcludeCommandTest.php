<?php

namespace MODX\CLI\Tests\Integration\Commands\Config;

/**
 * Integration test for config:rm-exclude-command command
 */
class RmExcludeCommandTest extends ConfigTestCase
{
    public function testRmExcludeCommandWhenNotExcluded()
    {
        $commandClass = 'MODX\\CLI\\Command\\FakeCommand';

        $process = $this->runConfigCommand([
            'config:rm-exclude-command',
            $commandClass,
            '--json',
        ]);

        $this->assertSame(0, $process->getExitCode());
        $data = json_decode($process->getOutput(), true);
        $this->assertTrue($data['success']);
        $this->assertFalse($data['removed']);
    }
}
