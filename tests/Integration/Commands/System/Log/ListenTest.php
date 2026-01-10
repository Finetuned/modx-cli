<?php

namespace MODX\CLI\Tests\Integration\Commands\System\Log;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;
use Symfony\Component\Process\Process;

/**
 * Integration test for system:log:listen command
 */
class ListenTest extends BaseIntegrationTest
{
    public function testLogListenOutputsInitialJsonSnapshot()
    {
        $command = [
            'php',
            $this->binPath,
            '-s',
            $this->instanceAlias,
            'system:log:listen',
            '--json',
            '--limit=1',
            '--interval=1',
        ];

        $process = new Process($command, $this->modxPath);
        $process->setEnv([
            'MODX_CONFIG_KEY' => 'config',
        ]);

        $process->start();
        usleep(500000);
        $process->stop(1, SIGINT);

        $output = trim($process->getOutput());
        $this->assertNotSame('', $output, 'Expected JSON output from system:log:listen.');

        $data = json_decode($output, true);
        $this->assertIsArray($data, 'Command output is not valid JSON: ' . $output);
        $this->assertArrayHasKey('total', $data);
        $this->assertArrayHasKey('results', $data);
    }
}
