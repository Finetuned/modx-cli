<?php

namespace MODX\CLI\Tests\Integration\Commands\Snippet;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;

/**
 * Integration test for snippet:remove command
 */
class SnippetRemoveTest extends BaseIntegrationTest
{
    /**
     * Test that snippet:remove deletes a snippet
     */
    public function testSnippetRemoveExecutesSuccessfully()
    {
        $snippetName = 'IntegrationTestSnippet_' . uniqid();

        // Create snippet first
        $this->executeCommandSuccessfully([
            'snippet:create',
            $snippetName,
            '--snippet=<?php return "Test"; ?>'
        ]);

        // Get the snippet ID
        $rows = $this->queryDatabase('SELECT id FROM ' . $this->snippetsTable . ' WHERE name = ?', [$snippetName]);
        $snippetId = $rows[0]['id'];

        // Verify snippet exists
        $beforeCount = $this->countTableRows($this->snippetsTable, 'id = ?', [$snippetId]);
        $this->assertEquals(1, $beforeCount);

        // Remove snippet
        $process = $this->executeCommandSuccessfully([
            'snippet:remove',
            $snippetId
        ]);

        $output = $process->getOutput();
        $this->assertStringContainsString('removed successfully', $output);

        // Verify snippet no longer exists
        $afterCount = $this->countTableRows($this->snippetsTable, 'id = ?', [$snippetId]);
        $this->assertEquals(0, $afterCount);
    }

    /**
     * Test snippet:remove with JSON output
     */
    public function testSnippetRemoveReturnsValidJson()
    {
        $snippetName = 'IntegrationTestSnippet_' . uniqid();

        // Create snippet
        $this->executeCommandSuccessfully([
            'snippet:create',
            $snippetName
        ]);

        // Get snippet ID
        $rows = $this->queryDatabase('SELECT id FROM ' . $this->snippetsTable . ' WHERE name = ?', [$snippetName]);
        $snippetId = $rows[0]['id'];

        // Remove snippet with JSON
        $data = $this->executeCommandJson([
            'snippet:remove',
            $snippetId
        ]);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('success', $data);
        $this->assertTrue($data['success']);
    }

    /**
     * Test error handling for non-existent snippet
     */
    public function testSnippetRemoveWithInvalidId()
    {
        $process = $this->executeCommand([
            'snippet:remove',
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
        $this->queryDatabase('DELETE FROM ' . $this->snippetsTable . ' WHERE name LIKE ?', ['IntegrationTestSnippet_%']);
        parent::tearDown();
    }
}
