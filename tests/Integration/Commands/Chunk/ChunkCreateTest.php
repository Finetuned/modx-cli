<?php

namespace MODX\CLI\Tests\Integration\Commands\Chunk;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;

/**
 * Integration test for chunk:create command
 */
class ChunkCreateTest extends BaseIntegrationTest
{
    /**
     * Test that chunk:create executes successfully
     */
    public function testChunkCreateExecutesSuccessfully()
    {
        $chunkName = 'IntegrationTestChunk_' . uniqid();
        
        $process = $this->executeCommandSuccessfully([
            'chunk:create',
            $chunkName,
            '--category=0'
        ]);
        
        $output = $process->getOutput();
        $this->assertStringContainsString('created successfully', $output);
        
        // Cleanup
        $this->queryDatabase('DELETE FROM ' . $this->chunksTable . ' WHERE name = ?', [$chunkName]);
    }

    /**
     * Test chunk creation with JSON output
     */
    public function testChunkCreateReturnsValidJson()
    {
        $chunkName = 'IntegrationTestChunk_' . uniqid();
        
        $data = $this->executeCommandJson([
            'chunk:create',
            $chunkName,
            '--snippet=<div>Test</div>'
        ]);
        
        $this->assertIsArray($data);
        $this->assertArrayHasKey('success', $data);
        $this->assertTrue($data['success']);
        
        // Cleanup
        $this->queryDatabase('DELETE FROM ' . $this->chunksTable . ' WHERE name = ?', [$chunkName]);
    }

    /**
     * Test that created chunk appears in database
     */
    public function testChunkCreationPersistsToDatabase()
    {
        $chunkName = 'IntegrationTestChunk_' . uniqid();
        $content = '<div class="test">[[+content]]</div>';
        
        $beforeCount = $this->countTableRows($this->chunksTable, 'name = ?', [$chunkName]);
        $this->assertEquals(0, $beforeCount);
        
        $this->executeCommandSuccessfully([
            'chunk:create',
            $chunkName,
            '--snippet=' . $content
        ]);
        
        $afterCount = $this->countTableRows($this->chunksTable, 'name = ?', [$chunkName]);
        $this->assertEquals(1, $afterCount);
        
        // Verify chunk content
        $rows = $this->queryDatabase('SELECT snippet FROM ' . $this->chunksTable . ' WHERE name = ?', [$chunkName]);
        $this->assertEquals($content, $rows[0]['snippet']);
        
        // Cleanup
        $this->queryDatabase('DELETE FROM ' . $this->chunksTable . ' WHERE name = ?', [$chunkName]);
    }

    /**
     * Test chunk creation with category
     */
    public function testChunkCreationWithCategory()
    {
        $categoryName = 'IntegrationTestCategory_' . uniqid();
        $chunkName = 'IntegrationTestChunk_' . uniqid();
        
        // Create category first
        $this->executeCommandSuccessfully([
            'category:create',
            $categoryName
        ]);
        
        // Get category ID
        $catRows = $this->queryDatabase('SELECT id FROM ' . $this->categoriesTable . ' WHERE category = ?', [$categoryName]);
        $categoryId = $catRows[0]['id'];
        
        // Create chunk with category
        $this->executeCommandSuccessfully([
            'chunk:create',
            $chunkName,
            '--category=' . $categoryId
        ]);
        
        // Verify chunk has correct category
        $chunkRows = $this->queryDatabase('SELECT category FROM ' . $this->chunksTable . ' WHERE name = ?', [$chunkName]);
        $this->assertEquals($categoryId, $chunkRows[0]['category']);
        
        // Cleanup
        $this->queryDatabase('DELETE FROM ' . $this->chunksTable . ' WHERE name = ?', [$chunkName]);
        $this->queryDatabase('DELETE FROM ' . $this->categoriesTable . ' WHERE id = ?', [$categoryId]);
    }

    /**
     * Test chunk creation with description, locked, and static file options
     */
    public function testChunkCreationWithAdditionalOptions()
    {
        $chunkName = 'IntegrationTestChunk_' . uniqid();
        $description = 'Integration test description';
        $staticFile = 'core/components/test/chunks/example.tpl';

        $this->executeCommandSuccessfully([
            'chunk:create',
            $chunkName,
            '--description=' . $description,
            '--locked=1',
            '--static=1',
            '--static_file=' . $staticFile
        ]);

        $rows = $this->queryDatabase(
            'SELECT description, locked, static, static_file FROM ' . $this->chunksTable . ' WHERE name = ?',
            [$chunkName]
        );

        $this->assertEquals($description, $rows[0]['description']);
        $this->assertEquals(1, (int) $rows[0]['locked']);
        $this->assertEquals(1, (int) $rows[0]['static']);
        $this->assertEquals($staticFile, $rows[0]['static_file']);

        $this->queryDatabase('DELETE FROM ' . $this->chunksTable . ' WHERE name = ?', [$chunkName]);
    }

    /**
     * Test error handling for duplicate chunk name
     */
    public function testChunkCreationWithDuplicateName()
    {
        $chunkName = 'IntegrationTestChunk_' . uniqid();
        
        // Create first chunk
        $this->executeCommandSuccessfully([
            'chunk:create',
            $chunkName
        ]);
        
        // Try to create duplicate
        $process = $this->executeCommand([
            'chunk:create',
            $chunkName
        ]);
        
        $output = $process->getOutput();
        $this->assertNotEmpty($output);
        
        // Cleanup
        $this->queryDatabase('DELETE FROM ' . $this->chunksTable . ' WHERE name = ?', [$chunkName]);
    }

    /**
     * Clean up test data
     */
    protected function tearDown(): void
    {
        // Remove any leftover test chunks
        $this->queryDatabase('DELETE FROM ' . $this->chunksTable . ' WHERE name LIKE ?', ['IntegrationTestChunk_%']);
        $this->queryDatabase('DELETE FROM ' . $this->categoriesTable . ' WHERE category LIKE ?', ['IntegrationTestCategory_%']);
        
        parent::tearDown();
    }
}
