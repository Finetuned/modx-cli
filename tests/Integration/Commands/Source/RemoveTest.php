<?php

namespace MODX\CLI\Tests\Integration\Commands\Source;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;

/**
 * Integration test for source:remove command
 */
class RemoveTest extends BaseIntegrationTest
{
    /**
     * Test that source:remove executes successfully
     */
    public function testSourceRemoveExecutesSuccessfully()
    {
        $sourceName = 'integtest_' . uniqid();
        
        // Create test source
        $this->queryDatabase(
            'INSERT INTO ' . $this->getTableName('media_sources') . ' (name, description, class_key) VALUES (?, ?, ?)',
            [$sourceName, 'Test Description', 'MODX\\Revolution\\Sources\\modFileMediaSource']
        );
        
        $sourceId = $this->queryDatabase('SELECT id FROM ' . $this->getTableName('media_sources') . ' WHERE name = ?', [$sourceName])[0]['id'];
        
        $process = $this->executeCommandSuccessfully([
            'source:remove',
            $sourceId,
            '--force'
        ]);
        
        $output = $process->getOutput();
        $this->assertStringContainsString('removed successfully', $output);
        
        // Verify removal
        $count = $this->countTableRows($this->getTableName('media_sources'), 'id = ?', [$sourceId]);
        $this->assertEquals(0, $count);
    }

    /**
     * Test source:remove with JSON output
     */
    public function testSourceRemoveReturnsValidJson()
    {
        $sourceName = 'integtest_' . uniqid();
        
        // Create test source
        $this->queryDatabase(
            'INSERT INTO ' . $this->getTableName('media_sources') . ' (name, description, class_key) VALUES (?, ?, ?)',
            [$sourceName, 'Test Description', 'MODX\\Revolution\\Sources\\modFileMediaSource']
        );
        
        $sourceId = $this->queryDatabase('SELECT id FROM ' . $this->getTableName('media_sources') . ' WHERE name = ?', [$sourceName])[0]['id'];
        
        $data = $this->executeCommandJson([
            'source:remove',
            $sourceId,
            '--force'
        ]);
        
        $this->assertIsArray($data);
        $this->assertArrayHasKey('success', $data);
        $this->assertTrue($data['success']);
    }

    /**
     * Test that removal actually deletes from database
     */
    public function testSourceRemovalDeletesFromDatabase()
    {
        $sourceName = 'integtest_' . uniqid();
        
        // Create test source
        $this->queryDatabase(
            'INSERT INTO ' . $this->getTableName('media_sources') . ' (name, description, class_key) VALUES (?, ?, ?)',
            [$sourceName, 'Test Description', 'MODX\\Revolution\\Sources\\modFileMediaSource']
        );
        
        $sourceId = $this->queryDatabase('SELECT id FROM ' . $this->getTableName('media_sources') . ' WHERE name = ?', [$sourceName])[0]['id'];
        
        $beforeCount = $this->countTableRows($this->getTableName('media_sources'), 'id = ?', [$sourceId]);
        $this->assertEquals(1, $beforeCount);
        
        $this->executeCommandSuccessfully([
            'source:remove',
            $sourceId,
            '--force'
        ]);
        
        $afterCount = $this->countTableRows($this->getTableName('media_sources'), 'id = ?', [$sourceId]);
        $this->assertEquals(0, $afterCount);
    }

    /**
     * Test removal with non-existent source
     */
    public function testSourceRemoveWithNonExistentSource()
    {
        $process = $this->executeCommand([
            'source:remove',
            '999999',
            '--force'
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