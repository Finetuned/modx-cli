<?php

namespace MODX\CLI\Tests\Integration\Commands\System\Events;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;

/**
 * Integration test for system:event:delete command
 */
class DeleteTest extends BaseIntegrationTest
{
    protected string $eventsTable;

    protected function setUp(): void
    {
        parent::setUp();
        $this->eventsTable = $this->getTableName('system_eventnames');
    }

    public function testEventDeleteReturnsNotFoundForCustomEvent()
    {
        $eventName = 'IntegrationTestEvent_' . uniqid();

        $this->executeCommandSuccessfully([
            'system:event:create',
            $eventName,
            '--service=1'
        ]);

        $primaryKey = $this->getPrimaryKeyColumn();
        $rows = $this->queryDatabase(
            'SELECT ' . $primaryKey . ' FROM ' . $this->eventsTable . ' WHERE name = ?',
            [$eventName]
        );
        $eventId = $rows[0][$primaryKey];

        $process = $this->executeCommand([
            'system:event:delete',
            $eventId,
            '--force'
        ]);

        $this->assertNotEquals(0, $process->getExitCode());
        $this->assertNotEmpty($process->getOutput());
    }

    public function testEventDeleteWithInvalidId()
    {
        $process = $this->executeCommand([
            'system:event:delete',
            '999999',
            '--force'
        ]);

        $this->assertEquals(0, $process->getExitCode());
        $this->assertStringContainsString('not found', $process->getOutput());
    }

    protected function getPrimaryKeyColumn(): string
    {
        $columns = $this->queryDatabase('SHOW COLUMNS FROM ' . $this->eventsTable);
        foreach ($columns as $column) {
            if (($column['Key'] ?? '') === 'PRI') {
                return $column['Field'];
            }
        }

        foreach ($columns as $column) {
            if (isset($column['Extra']) && $column['Extra'] === 'auto_increment') {
                return $column['Field'];
            }
        }

        return 'id';
    }

    protected function tearDown(): void
    {
        $this->queryDatabase(
            'DELETE FROM ' . $this->eventsTable . ' WHERE name LIKE ?',
            ['IntegrationTestEvent_%']
        );
        parent::tearDown();
    }
}
