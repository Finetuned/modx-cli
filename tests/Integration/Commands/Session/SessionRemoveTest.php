<?php

namespace MODX\CLI\Tests\Integration\Commands\Session;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;

/**
 * Integration test for session:remove command
 */
class SessionRemoveTest extends BaseIntegrationTest
{
    protected string $sessionsTable;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sessionsTable = $this->getTableName('session');
    }

    public function testSessionRemoveWithInvalidId()
    {
        $process = $this->executeCommand([
            'session:remove',
            'integration-invalid-session',
            '--force',
            '--json'
        ]);

        $this->assertNotEquals(0, $process->getExitCode());
        $output = $process->getOutput();
        $this->assertNotEmpty($output);
    }
}
