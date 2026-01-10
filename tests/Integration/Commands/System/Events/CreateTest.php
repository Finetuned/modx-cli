<?php

namespace MODX\CLI\Tests\Integration\Commands\System\Events;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;

/**
 * Integration test for system:event:create command
 */
class CreateTest extends BaseIntegrationTest
{
    protected string $eventsTable;

    protected function setUp(): void
    {
        parent::setUp();
        $this->eventsTable = $this->getTableName('system_eventnames');
    }

    public function testEventCreateExecutesSuccessfully()
    {
        $eventName = 'IntegrationTestEvent_' . uniqid();

        $process = $this->executeCommandSuccessfully([
            'system:event:create',
            $eventName,
            '--service=1',
            '--groupname=Integration'
        ]);

        $output = $process->getOutput();
        $this->assertStringContainsString('Event created successfully', $output);

        $rows = $this->queryDatabase(
            'SELECT name, service, groupname FROM ' . $this->eventsTable . ' WHERE name = ?',
            [$eventName]
        );
        $this->assertEquals($eventName, $rows[0]['name']);
        $this->assertEquals(1, (int) $rows[0]['service']);
        $this->assertEquals('Integration', $rows[0]['groupname']);

        $this->queryDatabase('DELETE FROM ' . $this->eventsTable . ' WHERE name = ?', [$eventName]);
    }

    public function testEventCreateReturnsValidJson()
    {
        $eventName = 'IntegrationTestEvent_' . uniqid();

        $data = $this->executeCommandJson([
            'system:event:create',
            $eventName,
            '--service=1'
        ]);

        $this->assertArrayHasKey('success', $data);
        $this->assertTrue($data['success']);

        $this->queryDatabase('DELETE FROM ' . $this->eventsTable . ' WHERE name = ?', [$eventName]);
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
