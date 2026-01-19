<?php

namespace MODX\CLI\Tests\Integration\Commands\TV;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;

/**
 * Integration test for tv:remove command
 */
class TVRemoveTest extends BaseIntegrationTest
{
    /**
     * Test that tv:remove deletes a TV
     */
    public function testTVRemoveExecutesSuccessfully()
    {
        $tvName = 'IntegrationTestTV_' . uniqid();

        // Create TV first
        $this->executeCommandSuccessfully([
            'tv:create',
            $tvName,
            '--type=text',
            '--caption=Test TV'
        ]);

        // Get the TV ID
        $rows = $this->queryDatabase('SELECT id FROM ' . $this->tvsTable . ' WHERE name = ?', [$tvName]);
        $tvId = $rows[0]['id'];

        // Verify TV exists
        $beforeCount = $this->countTableRows($this->tvsTable, 'id = ?', [$tvId]);
        $this->assertEquals(1, $beforeCount);

        // Remove TV
        $process = $this->executeCommandSuccessfully([
            'tv:remove',
            $tvId
        ]);

        $output = $process->getOutput();
        $this->assertStringContainsString('removed successfully', $output);

        // Verify TV no longer exists
        $afterCount = $this->countTableRows($this->tvsTable, 'id = ?', [$tvId]);
        $this->assertEquals(0, $afterCount);
    }

    /**
     * Test tv:remove with JSON output
     */
    public function testTVRemoveReturnsValidJson()
    {
        $tvName = 'IntegrationTestTV_' . uniqid();

        // Create TV
        $this->executeCommandSuccessfully([
            'tv:create',
            $tvName,
            '--type=text'
        ]);

        // Get TV ID
        $rows = $this->queryDatabase('SELECT id FROM ' . $this->tvsTable . ' WHERE name = ?', [$tvName]);
        $tvId = $rows[0]['id'];

        // Remove TV with JSON
        $data = $this->executeCommandJson([
            'tv:remove',
            $tvId
        ]);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('success', $data);
        $this->assertTrue($data['success']);
    }

    /**
     * Test error handling for non-existent TV
     */
    public function testTVRemoveWithInvalidId()
    {
        $process = $this->executeCommand([
            'tv:remove',
            '999999'
        ]);

        $output = $process->getOutput();
        $this->assertNotEmpty($output);
    }

    /**
     * Clean up test data
     */
    protected function tearDown(): void
    {
        $this->queryDatabase('DELETE FROM ' . $this->tvsTable . ' WHERE name LIKE ?', ['IntegrationTestTV_%']);
        parent::tearDown();
    }
}
