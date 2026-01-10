<?php

namespace MODX\CLI\Tests\Integration\Commands\System\Locks;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;

/**
 * Integration test for system:locks:read command
 */
class ReadTest extends BaseIntegrationTest
{
    public function testLocksReadWithInvalidKey()
    {
        $process = $this->executeCommand([
            'system:locks:read',
            'integration-invalid-lock'
        ]);

        $this->assertNotEquals(0, $process->getExitCode());
        $this->assertStringContainsString('not found', $process->getOutput());
    }
}
