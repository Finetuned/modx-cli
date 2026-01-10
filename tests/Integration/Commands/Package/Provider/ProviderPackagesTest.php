<?php

namespace MODX\CLI\Tests\Integration\Commands\Package\Provider;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;

/**
 * Integration test for package:provider:packages command
 */
class ProviderPackagesTest extends BaseIntegrationTest
{
    public function testProviderPackagesWithInvalidId()
    {
        $process = $this->executeCommand([
            'package:provider:packages',
            '999999'
        ]);

        $this->assertNotEquals(0, $process->getExitCode());
        $this->assertNotEmpty($process->getOutput());
    }
}
