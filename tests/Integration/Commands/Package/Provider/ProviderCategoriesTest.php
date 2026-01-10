<?php

namespace MODX\CLI\Tests\Integration\Commands\Package\Provider;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;

/**
 * Integration test for package:provider:categories command
 */
class ProviderCategoriesTest extends BaseIntegrationTest
{
    public function testProviderCategoriesWithInvalidId()
    {
        $process = $this->executeCommand([
            'package:provider:categories',
            '999999'
        ]);

        $this->assertNotEquals(0, $process->getExitCode());
        $this->assertNotEmpty($process->getOutput());
    }
}
