<?php

namespace MODX\CLI\Tests\Integration\Commands\Snippet;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;

/**
 * Integration test for snippet:list command
 */
class SnippetListTest extends BaseIntegrationTest
{
    /**
     * Test that snippet:list executes successfully
     */
    public function testSnippetListExecutesSuccessfully()
    {
        $snippetName = 'IntegrationTestSnippet_' . uniqid();
        
        // Create a test snippet first
        $this->executeCommandSuccessfully([
            'snippet:create',
            $snippetName,
            '--snippet=<?php return "Test"; ?>'
        ]);
        
        // List snippets
        $process = $this->executeCommandSuccessfully([
            'snippet:list'
        ]);
        
        $output = $process->getOutput();
        $this->assertStringContainsString($snippetName, $output);
        
        // Cleanup
        $this->queryDatabase('DELETE FROM modx_site_snippets WHERE name = ?', [$snippetName]);
    }

    /**
     * Test snippet:list with JSON output
     */
    public function testSnippetListReturnsValidJson()
    {
        $snippetName = 'IntegrationTestSnippet_' . uniqid();
        
        // Create test snippet
        $this->executeCommandSuccessfully([
            'snippet:create',
            $snippetName
        ]);
        
        // List with JSON
        $data = $this->executeCommandJson([
            'snippet:list'
        ]);
        
        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
        
        // Find our test snippet in the results
        $found = false;
        foreach ($data as $snippet) {
            if ($snippet['name'] === $snippetName) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, "Created snippet not found in list");
        
        // Cleanup
        $this->queryDatabase('DELETE FROM modx_site_snippets WHERE name = ?', [$snippetName]);
    }

    /**
     * Test snippet:list filtering by category
     */
    public function testSnippetListFilterByCategory()
    {
        $categoryName = 'IntegrationTestCategory_' . uniqid();
        $snippetName = 'IntegrationTestSnippet_' . uniqid();
        
        // Create category
        $this->executeCommandSuccessfully([
            'category:create',
            $categoryName
        ]);
        
        // Get category ID
        $catRows = $this->queryDatabase('SELECT id FROM modx_categories WHERE category = ?', [$categoryName]);
        $categoryId = $catRows[0]['id'];
        
        // Create snippet in category
        $this->executeCommandSuccessfully([
            'snippet:create',
            $snippetName,
            '--category=' . $categoryId
        ]);
        
        // List snippets with category filter
        $process = $this->executeCommandSuccessfully([
            'snippet:list',
            '--category=' . $categoryId
        ]);
        
        $output = $process->getOutput();
        $this->assertStringContainsString($snippetName, $output);
        
        // Cleanup
        $this->queryDatabase('DELETE FROM modx_site_snippets WHERE name = ?', [$snippetName]);
        $this->queryDatabase('DELETE FROM modx_categories WHERE id = ?', [$categoryId]);
    }

    /**
     * Test empty snippet list
     */
    public function testSnippetListWhenEmpty()
    {
        // Remove all test snippets first
        $this->queryDatabase('DELETE FROM modx_site_snippets WHERE name LIKE ?', ['IntegrationTestSnippet_%']);
        
        $process = $this->executeCommandSuccessfully([
            'snippet:list'
        ]);
        
        $output = $process->getOutput();
        $this->assertNotEmpty($output);
    }

    /**
     * Test snippet:list with limit
     */
    public function testSnippetListWithLimit()
    {
        // Create multiple test snippets
        $snippetNames = [];
        for ($i = 0; $i < 3; $i++) {
            $snippetName = 'IntegrationTestSnippet_' . uniqid() . '_' . $i;
            $snippetNames[] = $snippetName;
            
            $this->executeCommandSuccessfully([
                'snippet:create',
                $snippetName
            ]);
        }
        
        // List with limit
        $data = $this->executeCommandJson([
            'snippet:list',
            '--limit=2'
        ]);
        
        $this->assertIsArray($data);
        
        // Cleanup
        foreach ($snippetNames as $name) {
            $this->queryDatabase('DELETE FROM modx_site_snippets WHERE name = ?', [$name]);
        }
    }

    /**
     * Clean up test data
     */
    protected function tearDown(): void
    {
        $this->queryDatabase('DELETE FROM modx_site_snippets WHERE name LIKE ?', ['IntegrationTestSnippet_%']);
        $this->queryDatabase('DELETE FROM modx_categories WHERE category LIKE ?', ['IntegrationTestCategory_%']);
        parent::tearDown();
    }
}
