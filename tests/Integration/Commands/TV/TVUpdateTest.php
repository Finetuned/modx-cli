<?php

namespace MODX\CLI\Tests\Integration\Commands\TV;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;

/**
 * Integration test for tv:update command
 */
class TVUpdateTest extends BaseIntegrationTest
{
    /**
     * Test that tv:update modifies existing TV
     */
    public function testTVUpdateExecutesSuccessfully()
    {
        $tvName = 'IntegrationTestTV_' . uniqid();
        $newCaption = 'Updated TV Caption';

        // Create TV
        $this->executeCommandSuccessfully([
            'tv:create',
            $tvName,
            '--type=text',
            '--caption=Original Caption'
        ]);

        // Get TV ID
        $rows = $this->queryDatabase('SELECT id FROM ' . $this->tvsTable . ' WHERE name = ?', [$tvName]);
        $tvId = $rows[0]['id'];

        // Update TV
        $process = $this->executeCommandSuccessfully([
            'tv:update',
            $tvId,
            '--caption=' . $newCaption
        ]);

        $output = $process->getOutput();
        $this->assertStringContainsString('updated successfully', $output);

        // Verify update in database
        $updatedRows = $this->queryDatabase('SELECT caption FROM ' . $this->tvsTable . ' WHERE id = ?', [$tvId]);
        $this->assertEquals($newCaption, $updatedRows[0]['caption']);

        // Cleanup
        $this->queryDatabase('DELETE FROM ' . $this->tvsTable . ' WHERE id = ?', [$tvId]);
    }

    /**
     * Test tv:update with JSON output
     */
    public function testTVUpdateReturnsValidJson()
    {
        $tvName = 'IntegrationTestTV_' . uniqid();

        // Create TV
        $this->executeCommandSuccessfully([
            'tv:create',
            $tvName,
            '--type=text'
        ]);

        // Get TV ID
        $rows = $this->queryDatabase('SELECT id FROM ' . $this->tvsTable . ' WHERE name = ?', [$tvName]);
        $tvId = $rows[0]['id'];

        // Update with JSON
        $data = $this->executeCommandJson([
            'tv:update',
            $tvId,
            '--caption=Updated Caption'
        ]);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('success', $data);
        $this->assertTrue($data['success']);

        // Cleanup
        $this->queryDatabase('DELETE FROM ' . $this->tvsTable . ' WHERE id = ?', [$tvId]);
    }

    /**
     * Test tv:update changes category
     */
    public function testTVUpdateCategory()
    {
        $tvName = 'IntegrationTestTV_' . uniqid();
        $categoryName = 'IntegrationTestCategory_' . uniqid();

        // Create TV
        $this->executeCommandSuccessfully([
            'tv:create',
            $tvName,
            '--type=text'
        ]);

        // Create category
        $this->executeCommandSuccessfully([
            'category:create',
            $categoryName
        ]);

        // Get IDs
        $tvRows = $this->queryDatabase('SELECT id FROM ' . $this->tvsTable . ' WHERE name = ?', [$tvName]);
        $tvId = $tvRows[0]['id'];
        $catRows = $this->queryDatabase('SELECT id FROM ' . $this->categoriesTable . ' WHERE category = ?', [$categoryName]);
        $categoryId = $catRows[0]['id'];

        // Update TV category
        $this->executeCommandSuccessfully([
            'tv:update',
            $tvId,
            '--category=' . $categoryId
        ]);

        // Verify category updated
        $updatedRows = $this->queryDatabase('SELECT category FROM ' . $this->tvsTable . ' WHERE id = ?', [$tvId]);
        $this->assertEquals($categoryId, $updatedRows[0]['category']);

        // Cleanup
        $this->queryDatabase('DELETE FROM ' . $this->tvsTable . ' WHERE id = ?', [$tvId]);
        $this->queryDatabase('DELETE FROM ' . $this->categoriesTable . ' WHERE id = ?', [$categoryId]);
    }

    /**
     * Test tv:update changes default value
     */
    public function testTVUpdateDefaultValue()
    {
        $tvName = 'IntegrationTestTV_' . uniqid();
        $newDefault = 'Updated Default Value';

        // Create TV
        $this->executeCommandSuccessfully([
            'tv:create',
            $tvName,
            '--type=text'
        ]);

        // Get TV ID
        $rows = $this->queryDatabase('SELECT id FROM ' . $this->tvsTable . ' WHERE name = ?', [$tvName]);
        $tvId = $rows[0]['id'];

        // Update default value
        $this->executeCommandSuccessfully([
            'tv:update',
            $tvId,
            '--default_text=' . $newDefault
        ]);

        // Verify default value updated
        $updatedRows = $this->queryDatabase('SELECT default_text FROM ' . $this->tvsTable . ' WHERE id = ?', [$tvId]);
        $this->assertEquals($newDefault, $updatedRows[0]['default_text']);

        // Cleanup
        $this->queryDatabase('DELETE FROM ' . $this->tvsTable . ' WHERE id = ?', [$tvId]);
    }

    /**
     * Test tv:update with additional options
     */
    public function testTVUpdateWithAdditionalOptions()
    {
        $tvName = 'IntegrationTestTV_' . uniqid();
        $updatedName = 'IntegrationTestTVUpdated_' . uniqid();
        $description = 'Updated TV description';
        $elements = 'Choice 1||Choice 2';
        $staticFile = 'core/components/test/tvs/updated.tpl';

        $this->executeCommandSuccessfully([
            'tv:create',
            $tvName,
            '--type=listbox'
        ]);

        $rows = $this->queryDatabase('SELECT id FROM ' . $this->tvsTable . ' WHERE name = ?', [$tvName]);
        $tvId = $rows[0]['id'];

        $this->executeCommandSuccessfully([
            'tv:update',
            $tvId,
            '--name=' . $updatedName,
            '--description=' . $description,
            '--elements=' . $elements,
            '--rank=5',
            '--display=default',
            '--locked=1',
            '--static=1',
            '--static_file=' . $staticFile
        ]);

        $updatedRows = $this->queryDatabase(
            'SELECT name, description, elements, rank, display, locked, static, static_file FROM ' . $this->tvsTable . ' WHERE id = ?',
            [$tvId]
        );

        $this->assertEquals($updatedName, $updatedRows[0]['name']);
        $this->assertEquals($description, $updatedRows[0]['description']);
        $this->assertEquals($elements, $updatedRows[0]['elements']);
        $this->assertEquals(5, (int) $updatedRows[0]['rank']);
        $this->assertEquals('default', $updatedRows[0]['display']);
        $this->assertEquals(1, (int) $updatedRows[0]['locked']);
        $this->assertEquals(1, (int) $updatedRows[0]['static']);
        $this->assertEquals($staticFile, $updatedRows[0]['static_file']);

        $this->queryDatabase('DELETE FROM ' . $this->tvsTable . ' WHERE id = ?', [$tvId]);
    }

    /**
     * Test error handling for non-existent TV
     */
    public function testTVUpdateWithInvalidId()
    {
        $process = $this->executeCommand([
            'tv:update',
            '999999',
            '--caption=Test'
        ]);

        $output = $process->getOutput();
        $this->assertNotEmpty($output);
    }

    /**
     * Clean up test data
     */
    protected function tearDown(): void
    {
        $this->queryDatabase('DELETE FROM ' . $this->tvsTable . ' WHERE name LIKE ?', ['IntegrationTestTV_%']);
        $this->queryDatabase('DELETE FROM ' . $this->categoriesTable . ' WHERE category LIKE ?', ['IntegrationTestCategory_%']);
        parent::tearDown();
    }
}
