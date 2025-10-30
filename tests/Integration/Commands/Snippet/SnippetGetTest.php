<?php

namespace MODX\CLI\Tests\Integration\Commands\Snippet;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;

/**
 * Integration test for snippet:get command
 */
class SnippetGetTest extends BaseIntegrationTest
{
    /**
     * Test that snippet:get retrieves existing snippet
     */
    public function testSnippetGetExecutesSuccessfully()
    {
        $snippetName = 'IntegrationTestSnippet_' . uniqid();
        
        // Create snippet first
        $this->executeCommandSuccessfully([
            'snippet:create',
            $snippetName,
            '--snippet=<?php return "Test"; ?>'
        ]);
        
        // Get the snippet ID
        $rows = $this->queryDatabase('SELECT id FROM modx_site_snippets WHERE name = ?', [$snippetName]);
        $snippetId = $rows[0]['id'];
        
        // Get snippet
        $process = $this->executeCommandSuccessfully([
            'snippet:get',
            $snippetId
        ]);
        
        $output = $process->getOutput();
        $this->assertStringContainsString($snippetName, $output);
        
        // Cleanup
        $this->queryDatabase('DELETE FROM modx_site_snippets WHERE id = ?', [$snippetId]);
    }

    /**
     * Test snippet:get with JSON output
     */
    public function testSnippetGetReturnsValidJson()
    {
        $snippetName = 'IntegrationTestSnippet_' . uniqid();
        
        // Create snippet
        $this->executeCommandSuccessfully([
            'snippet:create',
            $snippetName
        ]);
        
        // Get snippet ID
        $rows = $this->queryDatabase('SELECT id FROM modx_site_snippets WHERE name = ?', [$snippetName]);
        $snippetId = $rows[0]['id'];
        
        // Get snippet with JSON
        $data = $this->executeCommandJson([
            'snippet:get',
            $snippetId
        ]);
        
        $this->assertIsArray($data);
        $this->assertArrayHasKey('id', $data);
        $this->assertEquals($snippetId, $data['id']);
        $this->assertEquals($snippetName, $data['name']);
        
        // Cleanup
        $this->queryDatabase('DELETE FROM modx_site_snippets WHERE id = ?', [$snippetId]);
    }

    /**
     * Test error handling for non-existent snippet
     */
    public function testSnippetGetWithInvalidId()
    {
        $process = $this->executeCommand([
            'snippet:get',
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
        $this->queryDatabase('DELETE FROM modx_site_snippets WHERE name LIKE ?', ['IntegrationTestSnippet_%']);
        parent::tearDown();
    }
}
