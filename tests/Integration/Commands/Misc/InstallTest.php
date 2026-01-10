<?php

namespace MODX\CLI\Tests\Integration\Commands\Misc;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;

/**
 * Integration test for install command
 */
class InstallTest extends BaseIntegrationTest
{
    public function testInstallReturnsDisabledMessage()
    {
        $process = $this->executeCommand([
            'install',
            '--json',
        ]);

        $data = json_decode($process->getOutput(), true);
        $this->assertIsArray($data);
        $this->assertFalse($data['success']);
        $this->assertStringContainsString('disabled', $data['message']);
    }
}
