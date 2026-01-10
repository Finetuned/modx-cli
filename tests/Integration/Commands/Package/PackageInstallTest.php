<?php

namespace MODX\CLI\Tests\Integration\Commands\Package;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;

/**
 * Integration test for package:install command
 */
class PackageInstallTest extends BaseIntegrationTest
{
    public function testPackageInstallWithInvalidSignatureAndNoDownload()
    {
        $process = $this->executeCommand([
            'package:install',
            'integration-invalid-signature',
            '--no-download'
        ]);

        $output = $process->getOutput();
        $this->assertStringContainsString('Package with signature', $output);
        $this->assertStringContainsString('Auto-download is disabled', $output);
    }
}
