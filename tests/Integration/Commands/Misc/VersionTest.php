<?php

namespace MODX\CLI\Tests\Integration\Commands\Misc;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;

/**
 * Integration test for version command
 */
class VersionTest extends BaseIntegrationTest
{
    public function testVersionReturnsCliVersion()
    {
        $data = $this->executeCommandJson([
            'version',
            '--json',
        ]);

        $this->assertTrue($data['success']);
        $this->assertArrayHasKey('cli_version', $data);
        $this->assertNotSame('', $data['cli_version']);
    }
}
