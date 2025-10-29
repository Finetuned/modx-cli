<?php

namespace MODX\CLI\Tests\Integration\Commands\Snippet;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;

/**
 * Integration test for snippet:create command
 */
class SnippetCreateTest extends BaseIntegrationTest
{
    /**
     * Test that snippet:create executes successfully
     */
    public function testSnippetCreateExecutesSuccessfully()
    {
        $snippetName = 'IntegrationTestSnippet_' . uniqid();
        
        $process = $this->executeCommandSuccessfully([
            'snippet:create',
            $snippetName,
            '--category=0'
        ]);
        
        $output = $process->getOutput();
        $this->assertStringContainsString('created successfully', $output);
        
        // Cleanup
        $this->queryDatabase('DELETE FROM modx_site_snippets WHERE name = ?', [$snippetName]);
    }

    /**
     * Test snippet creation with JSON output
     */
    public function testSnippetCreateReturnsValidJson()
    {
        $snippetName = 'IntegrationTestSnippet_' . uniqid();
        
        $data = $this->executeCommandJson([
            'snippet:create',
            $snippetName,
            '--snippet=<?php return "test";'
        ]);
        
        $this->assertIsArray($data);
        $this->assertArrayHasKey('success', $data);
        $this->assertTrue($data['success']);
        
        // Cleanup
        $this->queryDatabase('DELETE FROM modx_site_snippets WHERE name = ?', [$snippetName]);
    }

    /**
     * Test that created snippet appears in database
     */
    public function testSnippetCreationPersistsToDatabase()
    {
        $snippetName = 'IntegrationTestSnippet_' . uniqid();
        $code = '<?php return $modx->getOption("test", $scriptProperties, "default");';
        
        $beforeCount = $this->countTableRows('modx_site_snippets', 'name = ?', [$snippetName]);
        $this->assertEquals(0, $beforeCount);
        
        $this->executeCommandSuccessfully([
            'snippet:create',
            $snippetName,
            '--snippet=' . $code
        ]);
        
        $afterCount = $this->countTableRows('modx_site_snippets', 'name = ?', [$snippetName]);
        $this->assertEquals(1, $afterCount);
        
        // Verify snippet code
        $rows = $this->queryDatabase('SELECT snippet FROM modx_site_snippets WHERE name = ?', [$snippetName]);
        $this->assertEquals($code, $rows[0]['snippet']);
        
        // Cleanup
        $this->queryDatabase('DELETE FROM modx_site_snippets WHERE name = ?', [$snippetName]);
    }

    /**
     * Test snippet creation with category
     */
    public function testSnippetCreationWithCategory()
    {
        $categoryName = 'IntegrationTestCategory_' . uniqid();
        $snippetName = 'IntegrationTestSnippet_' . uniqid();
        
        // Create category first
        $this->executeCommandSuccessfully([
            'category:create',
            $categoryName
        ]);
        
        // Get category ID
        $catRows = $this->queryDatabase('SELECT id FROM modx_categories WHERE category = ?', [$categoryName]);
        $categoryId = $catRows[0]['id'];
        
        // Create snippet with category
        $this->executeCommandSuccessfully([
            'snippet:create',
            $snippetName,
            '--category=' . $categoryId
        ]);
        
        // Verify snippet has correct category
        $snippetRows = $this->queryDatabase('SELECT category FROM modx_site_snippets WHERE name = ?', [$snippetName]);
        $this->assertEquals($categoryId, $snippetRows[0]['category']);
        
        // Cleanup
        $this->queryDatabase('DELETE FROM modx_site_snippets WHERE name = ?', [$snippetName]);
        $this->queryDatabase('DELETE FROM modx_categories WHERE id = ?', [$categoryId]);
    }

    /**
     * Test error handling for duplicate snippet name
     */
    public function testSnippetCreationWithDuplicateName()
    {
        $snippetName = 'IntegrationTestSnippet_' . uniqid();
        
        // Create first snippet
        $this->executeCommandSuccessfully([
            'snippet:create',
            $snippetName
        ]);
        
        // Try to create duplicate
        $process = $this->executeCommand([
            'snippet:create',
            $snippetName
        ]);
        
        $output = $process->getOutput();
        $this->assertNotEmpty($output);
        
        // Cleanup
        $this->queryDatabase('DELETE FROM modx_site_snippets WHERE name = ?', [$snippetName]);
    }

    /**
     * Test snippet creation with description
     */
    public function testSnippetCreationWithDescription()
    {
        $snippetName = 'IntegrationTestSnippet_' . uniqid();
        $description = 'Test snippet for integration testing';
        
        $this->executeCommandSuccessfully([
            'snippet:create',
            $snippetName,
            '--description=' . $description
        ]);
        
        // Verify description in database
        $rows = $this->queryDatabase('SELECT description FROM modx_site_snippets WHERE name = ?', [$snippetName]);
        $this->assertEquals($description, $rows[0]['description']);
        
        // Cleanup
        $this->queryDatabase('DELETE FROM modx_site_snippets WHERE name = ?', [$snippetName]);
    }

    /**
     * Clean up test data
     */
    protected function tearDown(): void
    {
        // Remove any leftover test snippets
        $this->queryDatabase('DELETE FROM modx_site_snippets WHERE name LIKE ?', ['IntegrationTestSnippet_%']);
        $this->queryDatabase('DELETE FROM modx_categories WHERE category LIKE ?', ['IntegrationTestCategory_%']);
        
        parent::tearDown();
    }
}
