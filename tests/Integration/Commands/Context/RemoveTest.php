<?php

namespace MODX\CLI\Tests\Integration\Commands\Context;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;

/**
 * Integration test for context:remove command
 */
class RemoveTest extends BaseIntegrationTest
{
    /**
     * Test that context:remove executes successfully
     */
    public function testContextRemoveExecutesSuccessfully()
    {
        $contextKey = 'integtest-' . uniqid();
        
        // Create test context
        $this->queryDatabase(
            'INSERT INTO ' . $this->getTableName('context') . ' (`key`, name, description, rank) VALUES (?, ?, ?, ?)',
            [$contextKey, 'Test Context', 'Test Description', 0]
        );
        
        $process = $this->executeCommandSuccessfully([
            'context:remove',
            $contextKey,
            '--force'
        ]);
        
        $output = $process->getOutput();
        $this->assertStringContainsString('removed successfully', $output);
        
        // Verify removal
        $count = $this->countTableRows($this->getTableName('context'), '`key` = ?', [$contextKey]);
        $this->assertEquals(0, $count);
    }

    /**
     * Test context:remove with JSON output
     */
    public function testContextRemoveReturnsValidJson()
    {
        $contextKey = 'integtest-' . uniqid();
        
        // Create test context
        $this->queryDatabase(
            'INSERT INTO ' . $this->getTableName('context') . ' (`key`, name, description, rank) VALUES (?, ?, ?, ?)',
            [$contextKey, 'Test Context', 'Test Description', 0]
        );
        
        $data = $this->executeCommandJson([
            'context:remove',
            $contextKey,
            '--force'
        ]);
        
        $this->assertIsArray($data);
        $this->assertArrayHasKey('success', $data);
        $this->assertTrue($data['success']);
    }

    /**
     * Test that removal actually deletes from database
     */
    public function testContextRemovalDeletesFromDatabase()
    {
        $contextKey = 'integtest-' . uniqid();
        
        // Create test context
        $this->queryDatabase(
            'INSERT INTO ' . $this->getTableName('context') . ' (`key`, name, description, rank) VALUES (?, ?, ?, ?)',
            [$contextKey, 'Test Context', 'Test Description', 0]
        );
        
        $beforeCount = $this->countTableRows($this->getTableName('context'), '`key` = ?', [$contextKey]);
        $this->assertEquals(1, $beforeCount);
        
        $this->executeCommandSuccessfully([
            'context:remove',
            $contextKey,
            '--force'
        ]);
        
        $afterCount = $this->countTableRows($this->getTableName('context'), '`key` = ?', [$contextKey]);
        $this->assertEquals(0, $afterCount);
    }

    /**
     * Test removal with non-existent context
     */
    public function testContextRemoveWithNonExistentContext()
    {
        $process = $this->executeCommand([
            'context:remove',
            'nonexistent_' . uniqid(),
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
        // Remove any leftover test contexts
        $this->queryDatabase('DELETE FROM ' . $this->getTableName('context') . ' WHERE `key` LIKE ?', ['integtest-%']);
        
        parent::tearDown();
    }
}