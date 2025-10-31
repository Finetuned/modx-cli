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
        $rows = $this->queryDatabase('SELECT id FROM modx_site_htmlsnippets WHERE name = ?', [$chunkName]);
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
        $updatedRows = $this->queryDatabase('SELECT snippet FROM modx_site_htmlsnippets WHERE id = ?', [$chunkId]);
        $this->assertEquals($newContent, $updatedRows[0]['snippet']);
        
        // Cleanup
        $this->queryDatabase('DELETE FROM modx_site_htmlsnippets WHERE id = ?', [$chunkId]);
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
        $rows = $this->queryDatabase('SELECT id FROM modx_site_htmlsnippets WHERE name = ?', [$chunkName]);
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
        $this->queryDatabase('DELETE FROM modx_site_htmlsnippets WHERE id = ?', [$chunkId]);
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
        $chunkRows = $this->queryDatabase('SELECT id FROM modx_site_htmlsnippets WHERE name = ?', [$chunkName]);
        $chunkId = $chunkRows[0]['id'];
        $catRows = $this->queryDatabase('SELECT id FROM modx_categories WHERE category = ?', [$categoryName]);
        $categoryId = $catRows[0]['id'];
        
        // Update chunk category
        $this->executeCommandSuccessfully([
            'chunk:update',
            $chunkId,
            '--category=' . $categoryId
        ]);
        
        // Verify category updated
        $updatedRows = $this->queryDatabase('SELECT category FROM modx_site_htmlsnippets WHERE id = ?', [$chunkId]);
        $this->assertEquals($categoryId, $updatedRows[0]['category']);
        
        // Cleanup
        $this->queryDatabase('DELETE FROM modx_site_htmlsnippets WHERE id = ?', [$chunkId]);
        $this->queryDatabase('DELETE FROM modx_categories WHERE id = ?', [$categoryId]);
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
        $this->queryDatabase('DELETE FROM modx_site_htmlsnippets WHERE name LIKE ?', ['IntegrationTestChunk_%']);
        $this->queryDatabase('DELETE FROM modx_categories WHERE category LIKE ?', ['IntegrationTestCategory_%']);
        parent::tearDown();
    }
}
