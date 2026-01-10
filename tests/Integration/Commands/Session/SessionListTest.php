<?php

namespace MODX\CLI\Tests\Integration\Commands\Session;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;

/**
 * Integration test for session:list command
 */
class SessionListTest extends BaseIntegrationTest
{
    protected string $sessionsTable;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sessionsTable = $this->getTableName('session');
    }

    public function testSessionListReturnsValidJson()
    {
        $data = $this->executeCommandJson([
            'session:list',
            '--limit=1',
            '--start=0'
        ]);

        $this->assertArrayHasKey('results', $data);
        $this->assertIsArray($data['results']);
        $this->assertArrayHasKey('total', $data);
    }
}
