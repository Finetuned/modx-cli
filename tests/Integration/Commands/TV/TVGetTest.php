<?php

namespace MODX\CLI\Tests\Integration\Commands\TV;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;

/**
 * Integration test for tv:get command
 */
class TVGetTest extends BaseIntegrationTest
{
    /**
     * Test that tv:get retrieves existing TV
     */
    public function testTVGetExecutesSuccessfully()
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
        
        // Get TV
        $process = $this->executeCommandSuccessfully([
            'tv:get',
            $tvId
        ]);
        
        $output = $process->getOutput();
        $this->assertStringContainsString($tvName, $output);
        
        // Cleanup
        $this->queryDatabase('DELETE FROM ' . $this->tvsTable . ' WHERE id = ?', [$tvId]);
    }

    /**
     * Test tv:get with JSON output
     */
    public function testTVGetReturnsValidJson()
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
        
        // Get TV with JSON
        $data = $this->executeCommandJson([
            'tv:get',
            $tvId
        ]);
        
        $this->assertIsArray($data);
        $this->assertArrayHasKey('id', $data);
        $this->assertEquals($tvId, $data['id']);
        $this->assertEquals($tvName, $data['name']);
        
        // Cleanup
        $this->queryDatabase('DELETE FROM ' . $this->tvsTable . ' WHERE id = ?', [$tvId]);
    }

    /**
     * Test error handling for non-existent TV
     */
    public function testTVGetWithInvalidId()
    {
        $process = $this->executeCommand([
            'tv:get',
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
