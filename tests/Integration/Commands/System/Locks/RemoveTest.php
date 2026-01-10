<?php

namespace MODX\CLI\Tests\Integration\Commands\System\Locks;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;

/**
 * Integration test for system:locks:remove command
 */
class RemoveTest extends BaseIntegrationTest
{
    public function testLocksRemoveWithInvalidKey()
    {
        $process = $this->executeCommand([
            'system:locks:remove',
            'integration-invalid-lock',
            '--json'
        ]);

        $this->assertNotEquals(0, $process->getExitCode());
        $data = json_decode($process->getOutput(), true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('success', $data);
        $this->assertFalse($data['success']);
    }
}
