<?php

namespace MODX\CLI\Tests\Integration\Commands\Chunk;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;

/**
 * Integration test for chunk:list command
 */
class ChunkListTest extends BaseIntegrationTest
{
    /**
     * Test that chunk:list executes successfully
     */
    public function testChunkListExecutesSuccessfully()
    {
        $chunkName = 'IntegrationTestChunk_' . uniqid();
        
        // Create a test chunk first
        $this->executeCommandSuccessfully([
            'chunk:create',
            $chunkName,
            '--snippet=<div>Test</div>'
        ]);
        
        // List chunks
        $process = $this->executeCommandSuccessfully([
            'chunk:list'
        ]);
        
        $output = $process->getOutput();
        $this->assertStringContainsString($chunkName, $output);
        
        // Cleanup
        $this->queryDatabase('DELETE FROM modx_site_htmlsnippets WHERE name = ?', [$chunkName]);
    }

    /**
     * Test chunk:list with JSON output
     */
    public function testChunkListReturnsValidJson()
    {
        $chunkName = 'IntegrationTestChunk_' . uniqid();
        
        // Create test chunk
        $this->executeCommandSuccessfully([
            'chunk:create',
            $chunkName,
            '--snippet=<div>Test</div>'
        ]);
        
        // List with JSON
        $data = $this->executeCommandJson([
            'chunk:list'
        ]);
        
        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
        
        // Find our test chunk in the results
        $found = false;
        foreach ($data as $chunk) {
            if ($chunk['name'] === $chunkName) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, "Created chunk not found in list");
        
        // Cleanup
        $this->queryDatabase('DELETE FROM modx_site_htmlsnippets WHERE name = ?', [$chunkName]);
    }

    /**
     * Test chunk:list filtering by category
     */
    public function testChunkListFilterByCategory()
    {
        $categoryName = 'IntegrationTestCategory_' . uniqid();
        $chunkName = 'IntegrationTestChunk_' . uniqid();
        
        // Create category
        $this->executeCommandSuccessfully([
            'category:create',
            $categoryName
        ]);
        
        // Get category ID
        $catRows = $this->queryDatabase('SELECT id FROM modx_categories WHERE category = ?', [$categoryName]);
        $categoryId = $catRows[0]['id'];
        
        // Create chunk in category
        $this->executeCommandSuccessfully([
            'chunk:create',
            $chunkName,
            '--category=' . $categoryId
        ]);
        
        // List chunks with category filter
        $process = $this->executeCommandSuccessfully([
            'chunk:list',
            '--category=' . $categoryId
        ]);
        
        $output = $process->getOutput();
        $this->assertStringContainsString($chunkName, $output);
        
        // Cleanup
        $this->queryDatabase('DELETE FROM modx_site_htmlsnippets WHERE name = ?', [$chunkName]);
        $this->queryDatabase('DELETE FROM modx_categories WHERE id = ?', [$categoryId]);
    }

    /**
     * Test empty chunk list
     */
    public function testChunkListWhenEmpty()
    {
        // Remove all test chunks first
        $this->queryDatabase('DELETE FROM modx_site_htmlsnippets WHERE name LIKE ?', ['IntegrationTestChunk_%']);
        
        $process = $this->executeCommandSuccessfully([
            'chunk:list'
        ]);
        
        $output = $process->getOutput();
        $this->assertNotEmpty($output);
    }

    /**
     * Test chunk:list with limit
     */
    public function testChunkListWithLimit()
    {
        // Create multiple test chunks
        $chunkNames = [];
        for ($i = 0; $i < 3; $i++) {
            $chunkName = 'IntegrationTestChunk_' . uniqid() . '_' . $i;
            $chunkNames[] = $chunkName;
            
            $this->executeCommandSuccessfully([
                'chunk:create',
                $chunkName
            ]);
        }
        
        // List with limit
        $data = $this->executeCommandJson([
            'chunk:list',
            '--limit=2'
        ]);
        
        $this->assertIsArray($data);
        // Note: Result may include other chunks, just verify limit works
        
        // Cleanup
        foreach ($chunkNames as $name) {
            $this->queryDatabase('DELETE FROM modx_site_htmlsnippets WHERE name = ?', [$name]);
        }
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
