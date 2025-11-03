<?php

namespace MODX\CLI\Tests\Integration\Commands\Chunk;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;

/**
 * Integration test for chunk:remove command
 */
class ChunkRemoveTest extends BaseIntegrationTest
{
    /**
     * Test that chunk:remove deletes a chunk
     */
    public function testChunkRemoveExecutesSuccessfully()
    {
        $chunkName = 'IntegrationTestChunk_' . uniqid();
        
        // Create chunk first
        $this->executeCommandSuccessfully([
            'chunk:create',
            $chunkName,
            '--snippet=<div>Test</div>'
        ]);
        
        // Get the chunk ID
        $rows = $this->queryDatabase('SELECT id FROM ' . $this->chunksTable . ' WHERE name = ?', [$chunkName]);
        $chunkId = $rows[0]['id'];
        
        // Verify chunk exists
        $beforeCount = $this->countTableRows($this->chunksTable, 'id = ?', [$chunkId]);
        $this->assertEquals(1, $beforeCount);
        
        // Remove chunk
        $process = $this->executeCommandSuccessfully([
            'chunk:remove',
            $chunkId
        ]);
        
        $output = $process->getOutput();
        $this->assertStringContainsString('removed successfully', $output);
        
        // Verify chunk no longer exists
        $afterCount = $this->countTableRows($this->chunksTable, 'id = ?', [$chunkId]);
        $this->assertEquals(0, $afterCount);
    }

    /**
     * Test chunk:remove with JSON output
     */
    public function testChunkRemoveReturnsValidJson()
    {
        $chunkName = 'IntegrationTestChunk_' . uniqid();
        
        // Create chunk
        $this->executeCommandSuccessfully([
            'chunk:create',
            $chunkName
        ]);
        
        // Get chunk ID
        $rows = $this->queryDatabase('SELECT id FROM ' . $this->chunksTable . ' WHERE name = ?', [$chunkName]);
        $chunkId = $rows[0]['id'];
        
        // Remove chunk with JSON
        $data = $this->executeCommandJson([
            'chunk:remove',
            $chunkId
        ]);
        
        $this->assertIsArray($data);
        $this->assertArrayHasKey('success', $data);
        $this->assertTrue($data['success']);
    }

    /**
     * Test error handling for non-existent chunk
     */
    public function testChunkRemoveWithInvalidId()
    {
        $process = $this->executeCommand([
            'chunk:remove',
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
