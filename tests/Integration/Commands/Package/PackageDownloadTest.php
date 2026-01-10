<?php

namespace MODX\CLI\Tests\Integration\Commands\Package;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;

/**
 * Integration test for package:download command
 */
class PackageDownloadTest extends BaseIntegrationTest
{
    public function testPackageDownloadWithInvalidSignature()
    {
        $process = $this->executeCommand([
            'package:download',
            'integration-invalid-signature',
            '--force'
        ]);

        $output = $process->getOutput();
        $this->assertStringContainsString('Failed to retrieve package object', $output);
    }
}
