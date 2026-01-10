<?php

namespace MODX\CLI\Tests\Integration\Commands\Context;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;

/**
 * Integration test for context:update command
 */
class UpdateTest extends BaseIntegrationTest
{
    /**
     * Test that context:update executes successfully
     */
    public function testContextUpdateExecutesSuccessfully()
    {
        $contextKey = 'integtest-' . uniqid();
        
        // Create test context
        $this->queryDatabase(
            'INSERT INTO ' . $this->getTableName('context') . ' (`key`, name, description, rank) VALUES (?, ?, ?, ?)',
            [$contextKey, 'Original Name', 'Original Description', 0]
        );
        
        $process = $this->executeCommandSuccessfully([
            'context:update',
            $contextKey,
            '--name=Updated Name',
            '--description=Updated Description'
        ]);
        
        $output = $process->getOutput();
        $this->assertStringContainsString('updated successfully', $output);
        
        // Cleanup
        $this->queryDatabase('DELETE FROM ' . $this->getTableName('context') . ' WHERE `key` = ?', [$contextKey]);
    }

    /**
     * Test context:update with JSON output
     */
    public function testContextUpdateReturnsValidJson()
    {
        $contextKey = 'integtest-' . uniqid();
        
        // Create test context
        $this->queryDatabase(
            'INSERT INTO ' . $this->getTableName('context') . ' (`key`, name, description, rank) VALUES (?, ?, ?, ?)',
            [$contextKey, 'Original Name', 'Original Description', 0]
        );
        
        $data = $this->executeCommandJson([
            'context:update',
            $contextKey,
            '--name=Updated Name'
        ]);
        
        $this->assertIsArray($data);
        $this->assertArrayHasKey('success', $data);
        $this->assertTrue($data['success']);
        
        // Cleanup
        $this->queryDatabase('DELETE FROM ' . $this->getTableName('context') . ' WHERE `key` = ?', [$contextKey]);
    }

    /**
     * Test that update persists to database
     */
    public function testContextUpdatePersistsToDatabase()
    {
        $contextKey = 'integtest-' . uniqid();
        
        // Create test context
        $this->queryDatabase(
            'INSERT INTO ' . $this->getTableName('context') . ' (`key`, name, description, rank) VALUES (?, ?, ?, ?)',
            [$contextKey, 'Original Name', 'Original Description', 0]
        );
        
        $this->executeCommandSuccessfully([
            'context:update',
            $contextKey,
            '--name=New Name',
            '--description=New Description',
            '--rank=5'
        ]);
        
        // Verify updates
        $rows = $this->queryDatabase('SELECT * FROM ' . $this->getTableName('context') . ' WHERE `key` = ?', [$contextKey]);
        $this->assertEquals('New Name', $rows[0]['name']);
        $this->assertEquals('New Description', $rows[0]['description']);
        $this->assertEquals(5, $rows[0]['rank']);
        
        // Cleanup
        $this->queryDatabase('DELETE FROM ' . $this->getTableName('context') . ' WHERE `key` = ?', [$contextKey]);
    }

    /**
     * Test partial update (only one field)
     */
    public function testContextPartialUpdate()
    {
        $contextKey = 'integtest-' . uniqid();
        
        // Create test context
        $this->queryDatabase(
            'INSERT INTO ' . $this->getTableName('context') . ' (`key`, name, description, rank) VALUES (?, ?, ?, ?)',
            [$contextKey, 'Original Name', 'Original Description', 0]
        );
        
        $this->executeCommandSuccessfully([
            'context:update',
            $contextKey,
            '--description=Only Description Changed'
        ]);
        
        // Verify only description changed
        $rows = $this->queryDatabase('SELECT * FROM ' . $this->getTableName('context') . ' WHERE `key` = ?', [$contextKey]);
        $this->assertEquals('Original Name', $rows[0]['name']);
        $this->assertEquals('Only Description Changed', $rows[0]['description']);
        
        // Cleanup
        $this->queryDatabase('DELETE FROM ' . $this->getTableName('context') . ' WHERE `key` = ?', [$contextKey]);
    }

    /**
     * Test update with non-existent context
     */
    public function testContextUpdateWithNonExistentContext()
    {
        $process = $this->executeCommand([
            'context:update',
            'nonexistent_' . uniqid(),
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
        // Remove any leftover test contexts
        $this->queryDatabase('DELETE FROM ' . $this->getTableName('context') . ' WHERE `key` LIKE ?', ['integtest-%']);
        
        parent::tearDown();
    }
}