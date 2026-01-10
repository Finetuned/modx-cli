<?php

namespace MODX\CLI\Tests\Integration\Commands\Registry;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;

/**
 * Integration test for registry:send command
 */
class SendTest extends BaseIntegrationTest
{
    public function testRegistrySendReturnsValidJson()
    {
        $process = $this->executeCommand([
            'registry:send',
            'modxcli_test',
            'Hello registry',
            '--register=modxcli',
            '--json'
        ]);

        $data = json_decode($process->getOutput(), true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('success', $data);
    }
}
