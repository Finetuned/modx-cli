<?php

namespace MODX\CLI\Tests\Integration\Commands\Registry;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;

/**
 * Integration test for registry:read command
 */
class ReadTest extends BaseIntegrationTest
{
    public function testRegistryReadReturnsMessages()
    {
        $topic = 'modxcli_test';

        $sendProcess = $this->executeCommand([
            'registry:send',
            $topic,
            'Hello registry',
            '--register=modxcli',
            '--json'
        ]);
        $sendData = json_decode($sendProcess->getOutput(), true);
        $this->assertIsArray($sendData);

        $readProcess = $this->executeCommand([
            'registry:read',
            $topic,
            '--register=modxcli',
            '--keep',
            '--json'
        ]);

        $data = json_decode($readProcess->getOutput(), true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('success', $data);
    }
}
