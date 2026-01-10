<?php

namespace MODX\CLI\Tests\Integration\Commands\Source;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;

/**
 * Integration test for source:update command
 */
class UpdateTest extends BaseIntegrationTest
{
    /**
     * Test that source:update executes successfully
     */
    public function testSourceUpdateExecutesSuccessfully()
    {
        $sourceName = 'integtest_' . uniqid();
        
        // Create test source
        $this->queryDatabase(
            'INSERT INTO ' . $this->getTableName('media_sources') . ' (name, description, class_key) VALUES (?, ?, ?)',
            [$sourceName, 'Original Description', 'MODX\\Revolution\\Sources\\modFileMediaSource']
        );
        
        $sourceId = $this->queryDatabase('SELECT id FROM ' . $this->getTableName('media_sources') . ' WHERE name = ?', [$sourceName])[0]['id'];
        
        $process = $this->executeCommandSuccessfully([
            'source:update',
            $sourceId,
            '--description=Updated Description'
        ]);
        
        $output = $process->getOutput();
        $this->assertStringContainsString('updated successfully', $output);
        
        // Cleanup
        $this->queryDatabase('DELETE FROM ' . $this->getTableName('media_sources') . ' WHERE name = ?', [$sourceName]);
    }

    /**
     * Test source:update with JSON output
     */
    public function testSourceUpdateReturnsValidJson()
    {
        $sourceName = 'integtest_' . uniqid();
        
        // Create test source
        $this->queryDatabase(
            'INSERT INTO ' . $this->getTableName('media_sources') . ' (name, description, class_key) VALUES (?, ?, ?)',
            [$sourceName, 'Original Description', 'MODX\\Revolution\\Sources\\modFileMediaSource']
        );
        
        $sourceId = $this->queryDatabase('SELECT id FROM ' . $this->getTableName('media_sources') . ' WHERE name = ?', [$sourceName])[0]['id'];
        
        $data = $this->executeCommandJson([
            'source:update',
            $sourceId,
            '--name=Updated Name'
        ]);
        
        $this->assertIsArray($data);
        $this->assertArrayHasKey('success', $data);
        $this->assertTrue($data['success']);
        
        // Cleanup
        $this->queryDatabase('DELETE FROM ' . $this->getTableName('media_sources') . ' WHERE id = ?', [$sourceId]);
    }

    /**
     * Test that update persists to database
     */
    public function testSourceUpdatePersistsToDatabase()
    {
        $sourceName = 'integtest_' . uniqid();
        
        // Create test source
        $this->queryDatabase(
            'INSERT INTO ' . $this->getTableName('media_sources') . ' (name, description, class_key) VALUES (?, ?, ?)',
            [$sourceName, 'Original Description', 'MODX\\Revolution\\Sources\\modFileMediaSource']
        );
        
        $sourceId = $this->queryDatabase('SELECT id FROM ' . $this->getTableName('media_sources') . ' WHERE name = ?', [$sourceName])[0]['id'];
        
        $this->executeCommandSuccessfully([
            'source:update',
            $sourceId,
            '--description=New Description'
        ]);
        
        // Verify updates
        $rows = $this->queryDatabase('SELECT * FROM ' . $this->getTableName('media_sources') . ' WHERE id = ?', [$sourceId]);
        $this->assertEquals('New Description', $rows[0]['description']);
        
        // Cleanup
        $this->queryDatabase('DELETE FROM ' . $this->getTableName('media_sources') . ' WHERE id = ?', [$sourceId]);
    }

    /**
     * Test source update with source properties
     */
    public function testSourceUpdateWithSourceProperties()
    {
        $sourceName = 'integtest_' . uniqid();

        $this->queryDatabase(
            'INSERT INTO ' . $this->getTableName('media_sources') . ' (name, description, class_key) VALUES (?, ?, ?)',
            [$sourceName, 'Original Description', 'MODX\\Revolution\\Sources\\modFileMediaSource']
        );

        $sourceId = $this->queryDatabase(
            'SELECT id FROM ' . $this->getTableName('media_sources') . ' WHERE name = ?',
            [$sourceName]
        )[0]['id'];

        $properties = '{"updatedKey":"updatedValue"}';

        $this->executeCommandSuccessfully([
            'source:update',
            $sourceId,
            '--source-properties=' . $properties
        ]);

        $rows = $this->queryDatabase(
            'SELECT properties FROM ' . $this->getTableName('media_sources') . ' WHERE id = ?',
            [$sourceId]
        );
        $this->assertStringContainsString('updatedKey', (string) $rows[0]['properties']);

        $this->queryDatabase('DELETE FROM ' . $this->getTableName('media_sources') . ' WHERE id = ?', [$sourceId]);
    }

    /**
     * Test partial update (only one field)
     */
    public function testSourcePartialUpdate()
    {
        $sourceName = 'integtest_' . uniqid();
        
        // Create test source
        $this->queryDatabase(
            'INSERT INTO ' . $this->getTableName('media_sources') . ' (name, description, class_key) VALUES (?, ?, ?)',
            [$sourceName, 'Original Description', 'MODX\\Revolution\\Sources\\modFileMediaSource']
        );
        
        $sourceId = $this->queryDatabase('SELECT id FROM ' . $this->getTableName('media_sources') . ' WHERE name = ?', [$sourceName])[0]['id'];
        
        $this->executeCommandSuccessfully([
            'source:update',
            $sourceId,
            '--description=Only Description Changed'
        ]);
        
        // Verify only description changed
        $rows = $this->queryDatabase('SELECT * FROM ' . $this->getTableName('media_sources') . ' WHERE id = ?', [$sourceId]);
        $this->assertEquals($sourceName, $rows[0]['name']);
        $this->assertEquals('Only Description Changed', $rows[0]['description']);
        
        // Cleanup
        $this->queryDatabase('DELETE FROM ' . $this->getTableName('media_sources') . ' WHERE id = ?', [$sourceId]);
    }

    /**
     * Test update with non-existent source
     */
    public function testSourceUpdateWithNonExistentSource()
    {
        $process = $this->executeCommand([
            'source:update',
            '999999',
            '--name=Test'
        ]);
        
        $output = $process->getOutput();
        $this->assertNotEmpty($output);
    }

    /**
     * Clean up test data
     */
    protected function tearDown(): void
    {
        // Remove any leftover test sources
        $this->queryDatabase('DELETE FROM ' . $this->getTableName('media_sources') . ' WHERE name LIKE ?', ['integtest_%']);
        
        parent::tearDown();
    }
}
