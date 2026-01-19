<?php

namespace MODX\CLI\Tests\Integration\Commands\Snippet;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;

/**
 * Integration test for snippet:update command
 */
class SnippetUpdateTest extends BaseIntegrationTest
{
    /**
     * Test that snippet:update modifies existing snippet
     */
    public function testSnippetUpdateExecutesSuccessfully()
    {
        $snippetName = 'IntegrationTestSnippet_' . uniqid();
        $newCode = '<?php return "Updated code"; ?>';

        // Create snippet
        $this->executeCommandSuccessfully([
            'snippet:create',
            $snippetName,
            '--snippet=<?php return "Original"; ?>'
        ]);

        // Get snippet ID
        $rows = $this->queryDatabase('SELECT id FROM ' . $this->snippetsTable . ' WHERE name = ?', [$snippetName]);
        $snippetId = $rows[0]['id'];

        // Update snippet
        $process = $this->executeCommandSuccessfully([
            'snippet:update',
            $snippetId,
            '--snippet=' . $newCode
        ]);

        $output = $process->getOutput();
        $this->assertStringContainsString('updated successfully', $output);

        // Verify snippet code - MODX strips the <?php tag, so expect code without it
        $expectedCode = 'return "Updated code";';

        // Verify update in database
        $updatedRows = $this->queryDatabase('SELECT snippet FROM ' . $this->snippetsTable . ' WHERE id = ?', [$snippetId]);
        $this->assertEquals($expectedCode, $updatedRows[0]['snippet']);

        // Cleanup
        $this->queryDatabase('DELETE FROM ' . $this->snippetsTable . ' WHERE id = ?', [$snippetId]);
    }

    /**
     * Test snippet:update with JSON output
     */
    public function testSnippetUpdateReturnsValidJson()
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

        // Update with JSON
        $data = $this->executeCommandJson([
            'snippet:update',
            $snippetId,
            '--snippet=<?php return "Updated"; ?>'
        ]);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('success', $data);
        $this->assertTrue($data['success']);

        // Cleanup
        $this->queryDatabase('DELETE FROM ' . $this->snippetsTable . ' WHERE id = ?', [$snippetId]);
    }

    /**
     * Test snippet:update changes category
     */
    public function testSnippetUpdateCategory()
    {
        $snippetName = 'IntegrationTestSnippet_' . uniqid();
        $categoryName = 'IntegrationTestCategory_' . uniqid();

        // Create snippet
        $this->executeCommandSuccessfully([
            'snippet:create',
            $snippetName
        ]);

        // Create category
        $this->executeCommandSuccessfully([
            'category:create',
            $categoryName
        ]);

        // Get IDs
        $snippetRows = $this->queryDatabase('SELECT id FROM ' . $this->snippetsTable . ' WHERE name = ?', [$snippetName]);
        $snippetId = $snippetRows[0]['id'];
        $catRows = $this->queryDatabase('SELECT id FROM ' . $this->categoriesTable . ' WHERE category = ?', [$categoryName]);
        $categoryId = $catRows[0]['id'];

        // Update snippet category
        $this->executeCommandSuccessfully([
            'snippet:update',
            $snippetId,
            '--category=' . $categoryId
        ]);

        // Verify category updated
        $updatedRows = $this->queryDatabase('SELECT category FROM ' . $this->snippetsTable . ' WHERE id = ?', [$snippetId]);
        $this->assertEquals($categoryId, $updatedRows[0]['category']);

        // Cleanup
        $this->queryDatabase('DELETE FROM ' . $this->snippetsTable . ' WHERE id = ?', [$snippetId]);
        $this->queryDatabase('DELETE FROM ' . $this->categoriesTable . ' WHERE id = ?', [$categoryId]);
    }

    /**
     * Test snippet:update changes description
     */
    public function testSnippetUpdateDescription()
    {
        $snippetName = 'IntegrationTestSnippet_' . uniqid();
        $newDescription = 'Updated snippet description';

        // Create snippet
        $this->executeCommandSuccessfully([
            'snippet:create',
            $snippetName
        ]);

        // Get snippet ID
        $rows = $this->queryDatabase('SELECT id FROM ' . $this->snippetsTable . ' WHERE name = ?', [$snippetName]);
        $snippetId = $rows[0]['id'];

        // Update description
        $this->executeCommandSuccessfully([
            'snippet:update',
            $snippetId,
            '--description=' . $newDescription
        ]);

        // Verify description updated
        $updatedRows = $this->queryDatabase('SELECT description FROM ' . $this->snippetsTable . ' WHERE id = ?', [$snippetId]);
        $this->assertEquals($newDescription, $updatedRows[0]['description']);

        // Cleanup
        $this->queryDatabase('DELETE FROM ' . $this->snippetsTable . ' WHERE id = ?', [$snippetId]);
    }

    /**
     * Test snippet:update with additional options
     */
    public function testSnippetUpdateWithAdditionalOptions()
    {
        $snippetName = 'IntegrationTestSnippet_' . uniqid();
        $updatedName = 'IntegrationTestSnippetUpdated_' . uniqid();
        $staticFile = 'core/components/test/snippets/updated.php';

        $this->executeCommandSuccessfully([
            'snippet:create',
            $snippetName
        ]);

        $rows = $this->queryDatabase('SELECT id FROM ' . $this->snippetsTable . ' WHERE name = ?', [$snippetName]);
        $snippetId = $rows[0]['id'];

        $this->executeCommandSuccessfully([
            'snippet:update',
            $snippetId,
            '--name=' . $updatedName,
            '--locked=1',
            '--static=1',
            '--static_file=' . $staticFile
        ]);

        $updatedRows = $this->queryDatabase(
            'SELECT name, locked, static, static_file FROM ' . $this->snippetsTable . ' WHERE id = ?',
            [$snippetId]
        );
        $this->assertEquals($updatedName, $updatedRows[0]['name']);
        $this->assertEquals(1, (int) $updatedRows[0]['locked']);
        $this->assertEquals(1, (int) $updatedRows[0]['static']);
        $this->assertEquals($staticFile, $updatedRows[0]['static_file']);

        $this->queryDatabase('DELETE FROM ' . $this->snippetsTable . ' WHERE id = ?', [$snippetId]);
    }

    /**
     * Test error handling for non-existent snippet
     */
    public function testSnippetUpdateWithInvalidId()
    {
        $process = $this->executeCommand([
            'snippet:update',
            '999999',
            '--snippet=<?php return "Test"; ?>'
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
        $this->queryDatabase('DELETE FROM ' . $this->categoriesTable . ' WHERE category LIKE ?', ['IntegrationTestCategory_%']);
        parent::tearDown();
    }
}
