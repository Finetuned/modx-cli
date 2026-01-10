<?php

namespace MODX\CLI\Tests\Integration\Commands\Misc;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;

/**
 * Integration test for find command
 */
class FindTest extends BaseIntegrationTest
{
    public function testFindReturnsJson()
    {
        $process = $this->executeCommand([
            'find',
            'test',
            '--json',
        ]);

        $data = json_decode($process->getOutput(), true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('results', $data);
        $this->assertArrayHasKey('total', $data);
    }
}
