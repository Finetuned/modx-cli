<?php

namespace MODX\CLI\Tests\Integration\Commands\Chunk;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;

/**
 * Integration test for chunk:update command
 */
class ChunkUpdateTest extends BaseIntegrationTest
{
    /**
     * Test that chunk:update modifies existing chunk
     */
    public function testChunkUpdateExecutesSuccessfully()
    {
        $chunkName = 'IntegrationTestChunk_' . uniqid();
        $newContent = '<div class="updated">Updated content</div>';
        
        // Create chunk
        $this->executeCommandSuccessfully([
            'chunk:create',
            $chunkName,
            '--snippet=<div>Original</div>'
        ]);
        
        // Get chunk ID
        $rows = $this->queryDatabase('SELECT id FROM ' . $this->chunksTable . ' WHERE name = ?', [$chunkName]);
        $chunkId = $rows[0]['id'];
        
        // Update chunk
        $process = $this->executeCommandSuccessfully([
            'chunk:update',
            $chunkId,
            '--snippet=' . $newContent
        ]);
        
        $output = $process->getOutput();
        $this->assertStringContainsString('updated successfully', $output);
        
        // Verify update in database
        $updatedRows = $this->queryDatabase('SELECT snippet FROM ' . $this->chunksTable . ' WHERE id = ?', [$chunkId]);
        $this->assertEquals($newContent, $updatedRows[0]['snippet']);
        
        // Cleanup
        $this->queryDatabase('DELETE FROM ' . $this->chunksTable . ' WHERE id = ?', [$chunkId]);
    }

    /**
     * Test chunk:update with JSON output
     */
    public function testChunkUpdateReturnsValidJson()
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
        
        // Update with JSON
        $data = $this->executeCommandJson([
            'chunk:update',
            $chunkId,
            '--snippet=<div>Updated</div>'
        ]);
        
        $this->assertIsArray($data);
        $this->assertArrayHasKey('success', $data);
        $this->assertTrue($data['success']);
        
        // Cleanup
        $this->queryDatabase('DELETE FROM ' . $this->chunksTable . ' WHERE id = ?', [$chunkId]);
    }

    /**
     * Test chunk:update changes category
     */
    public function testChunkUpdateCategory()
    {
        $chunkName = 'IntegrationTestChunk_' . uniqid();
        $categoryName = 'IntegrationTestCategory_' . uniqid();
        
        // Create chunk
        $this->executeCommandSuccessfully([
            'chunk:create',
            $chunkName
        ]);
        
        // Create category
        $this->executeCommandSuccessfully([
            'category:create',
            $categoryName
        ]);
        
        // Get IDs
        $chunkRows = $this->queryDatabase('SELECT id FROM ' . $this->chunksTable . ' WHERE name = ?', [$chunkName]);
        $chunkId = $chunkRows[0]['id'];
        $catRows = $this->queryDatabase('SELECT id FROM ' . $this->categoriesTable . ' WHERE category = ?', [$categoryName]);
        $categoryId = $catRows[0]['id'];
        
        // Update chunk category
        $this->executeCommandSuccessfully([
            'chunk:update',
            $chunkId,
            '--category=' . $categoryId
        ]);
        
        // Verify category updated
        $updatedRows = $this->queryDatabase('SELECT category FROM ' . $this->chunksTable . ' WHERE id = ?', [$chunkId]);
        $this->assertEquals($categoryId, $updatedRows[0]['category']);
        
        // Cleanup
        $this->queryDatabase('DELETE FROM ' . $this->chunksTable . ' WHERE id = ?', [$chunkId]);
        $this->queryDatabase('DELETE FROM ' . $this->categoriesTable . ' WHERE id = ?', [$categoryId]);
    }

    /**
     * Test chunk:update with additional options
     */
    public function testChunkUpdateWithAdditionalOptions()
    {
        $chunkName = 'IntegrationTestChunk_' . uniqid();
        $updatedName = 'IntegrationTestChunkUpdated_' . uniqid();
        $description = 'Updated integration description';
        $staticFile = 'core/components/test/chunks/updated.tpl';

        $this->executeCommandSuccessfully([
            'chunk:create',
            $chunkName
        ]);

        $rows = $this->queryDatabase('SELECT id FROM ' . $this->chunksTable . ' WHERE name = ?', [$chunkName]);
        $chunkId = $rows[0]['id'];

        $this->executeCommandSuccessfully([
            'chunk:update',
            $chunkId,
            '--name=' . $updatedName,
            '--description=' . $description,
            '--locked=1',
            '--static=1',
            '--static_file=' . $staticFile
        ]);

        $updatedRows = $this->queryDatabase(
            'SELECT name, description, locked, static, static_file FROM ' . $this->chunksTable . ' WHERE id = ?',
            [$chunkId]
        );

        $this->assertEquals($updatedName, $updatedRows[0]['name']);
        $this->assertEquals($description, $updatedRows[0]['description']);
        $this->assertEquals(1, (int) $updatedRows[0]['locked']);
        $this->assertEquals(1, (int) $updatedRows[0]['static']);
        $this->assertEquals($staticFile, $updatedRows[0]['static_file']);

        $this->queryDatabase('DELETE FROM ' . $this->chunksTable . ' WHERE id = ?', [$chunkId]);
    }

    /**
     * Test error handling for non-existent chunk
     */
    public function testChunkUpdateWithInvalidId()
    {
        $process = $this->executeCommand([
            'chunk:update',
            '999999',
            '--snippet=<div>Test</div>'
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
        $this->queryDatabase('DELETE FROM ' . $this->categoriesTable . ' WHERE category LIKE ?', ['IntegrationTestCategory_%']);
        parent::tearDown();
    }
}
