<?php

namespace MODX\CLI\Tests\Integration\Commands\Registry\Queue;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;

/**
 * Integration test for registry:queue:list command
 */
class GetListTest extends BaseIntegrationTest
{
    public function testRegistryQueueListReturnsValidJson()
    {
        $process = $this->executeCommand([
            'registry:queue:list',
            '--register=db',
            '--json'
        ]);

        $this->assertNotEquals(0, $process->getExitCode());
        $data = json_decode($process->getOutput(), true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('success', $data);
        $this->assertFalse($data['success']);
        $this->assertArrayHasKey('message', $data);
    }
}
