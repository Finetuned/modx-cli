<?php

namespace MODX\CLI\Tests\Integration\Commands\Chunk;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;

/**
 * Integration test for chunk:get command
 */
class ChunkGetTest extends BaseIntegrationTest
{
    /**
     * Test that chunk:get retrieves existing chunk
     */
    public function testChunkGetExecutesSuccessfully()
    {
        $chunkName = 'IntegrationTestChunk_' . uniqid();

        // Create chunk first
        $this->executeCommandSuccessfully([
            'chunk:create',
            $chunkName,
            '--snippet=<div>Test Content</div>'
        ]);

        // Get the chunk ID
        $rows = $this->queryDatabase('SELECT id FROM ' . $this->chunksTable . ' WHERE name = ?', [$chunkName]);
        $chunkId = $rows[0]['id'];

        // Get chunk
        $process = $this->executeCommandSuccessfully([
            'chunk:get',
            $chunkId
        ]);

        $output = $process->getOutput();
        $this->assertStringContainsString($chunkName, $output);

        // Cleanup
        $this->queryDatabase('DELETE FROM ' . $this->chunksTable . ' WHERE id = ?', [$chunkId]);
    }

    /**
     * Test chunk:get with JSON output
     */
    public function testChunkGetReturnsValidJson()
    {
        $chunkName = 'IntegrationTestChunk_' . uniqid();

        // Create chunk
        $this->executeCommandSuccessfully([
            'chunk:create',
            $chunkName,
            '--snippet=<div>Test</div>'
        ]);

        // Get chunk ID
        $rows = $this->queryDatabase('SELECT id FROM ' . $this->chunksTable . ' WHERE name = ?', [$chunkName]);
        $chunkId = $rows[0]['id'];

        // Get chunk with JSON
        $data = $this->executeCommandJson([
            'chunk:get',
            $chunkId
        ]);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('id', $data);
        $this->assertEquals($chunkId, $data['id']);
        $this->assertEquals($chunkName, $data['name']);

        // Cleanup
        $this->queryDatabase('DELETE FROM ' . $this->chunksTable . ' WHERE id = ?', [$chunkId]);
    }

    /**
     * Test error handling for non-existent chunk
     */
    public function testChunkGetWithInvalidId()
    {
        $process = $this->executeCommand([
            'chunk:get',
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
        $this->queryDatabase('DELETE FROM ' . $this->chunksTable . ' WHERE name LIKE ?', ['IntegrationTestChunk_%']);
        parent::tearDown();
    }
}
