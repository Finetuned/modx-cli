<?php

namespace MODX\CLI\Tests\Integration\Commands\Context;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;

/**
 * Integration test for context:create command
 */
class CreateTest extends BaseIntegrationTest
{
    /**
     * Test that context:create executes successfully
     */
    public function testContextCreateExecutesSuccessfully()
    {
        $contextKey = 'integtest_' . uniqid();
        
        $process = $this->executeCommandSuccessfully([
            'context:create',
            $contextKey,
            '--name=Integration Test Context',
            '--description=Test context for integration tests'
        ]);
        
        $output = $process->getOutput();
        $this->assertStringContainsString('created successfully', $output);
        $this->assertStringContainsString('Context key:', $output);
        
        // Cleanup
        $this->queryDatabase('DELETE FROM ' . $this->getTableName('context') . ' WHERE key = ?', [$contextKey]);
    }

    /**
     * Test context creation with JSON output
     */
    public function testContextCreateReturnsValidJson()
    {
        $contextKey = 'integtest_' . uniqid();
        
        $data = $this->executeCommandJson([
            'context:create',
            $contextKey,
            '--name=Integration Test Context'
        ]);
        
        $this->assertIsArray($data);
        $this->assertArrayHasKey('success', $data);
        $this->assertTrue($data['success']);
        
        if (isset($data['object'])) {
            $this->assertArrayHasKey('key', $data['object']);
            $this->assertEquals($contextKey, $data['object']['key']);
        }
        
        // Cleanup
        $this->queryDatabase('DELETE FROM ' . $this->getTableName('context') . ' WHERE key = ?', [$contextKey]);
    }

    /**
     * Test that created context appears in database
     */
    public function testContextCreationPersistsToDatabase()
    {
        $contextKey = 'integtest_' . uniqid();
        
        $beforeCount = $this->countTableRows($this->getTableName('context'), 'key = ?', [$contextKey]);
        $this->assertEquals(0, $beforeCount);
        
        $this->executeCommandSuccessfully([
            'context:create',
            $contextKey,
            '--name=Test Context',
            '--description=Test Description'
        ]);
        
        $afterCount = $this->countTableRows($this->getTableName('context'), 'key = ?', [$contextKey]);
        $this->assertEquals(1, $afterCount);
        
        // Verify context data
        $rows = $this->queryDatabase('SELECT * FROM ' . $this->getTableName('context') . ' WHERE key = ?', [$contextKey]);
        $this->assertCount(1, $rows);
        $this->assertEquals($contextKey, $rows[0]['key']);
        $this->assertEquals('Test Context', $rows[0]['name']);
        $this->assertEquals('Test Description', $rows[0]['description']);
        
        // Cleanup
        $this->queryDatabase('DELETE FROM ' . $this->getTableName('context') . ' WHERE key = ?', [$contextKey]);
    }

    /**
     * Test context creation with minimal parameters
     */
    public function testContextCreationWithMinimalParameters()
    {
        $contextKey = 'integtest_' . uniqid();
        
        $this->executeCommandSuccessfully([
            'context:create',
            $contextKey
        ]);
        
        // Verify context exists
        $rows = $this->queryDatabase('SELECT * FROM ' . $this->getTableName('context') . ' WHERE key = ?', [$contextKey]);
        $this->assertCount(1, $rows);
        $this->assertEquals($contextKey, $rows[0]['key']);
        
        // Cleanup
        $this->queryDatabase('DELETE FROM ' . $this->getTableName('context') . ' WHERE key = ?', [$contextKey]);
    }

    /**
     * Test error handling for duplicate context key
     */
    public function testContextCreationWithDuplicateKey()
    {
        $contextKey = 'integtest_' . uniqid();
        
        // Create first context
        $this->executeCommandSuccessfully([
            'context:create',
            $contextKey
        ]);
        
        // Try to create duplicate
        $process = $this->executeCommand([
            'context:create',
            $contextKey
        ]);
        
        // Should fail or handle duplicate appropriately
        $output = $process->getOutput();
        $this->assertNotEmpty($output);
        
        // Cleanup
        $this->queryDatabase('DELETE FROM ' . $this->getTableName('context') . ' WHERE key = ?', [$contextKey]);
    }

    /**
     * Test context creation with rank parameter
     */
    public function testContextCreationWithRank()
    {
        $contextKey = 'integtest_' . uniqid();
        
        $this->executeCommandSuccessfully([
            'context:create',
            $contextKey,
            '--rank=5'
        ]);
        
        // Verify rank is set correctly
        $rows = $this->queryDatabase('SELECT rank FROM ' . $this->getTableName('context') . ' WHERE key = ?', [$contextKey]);
        $this->assertEquals(5, $rows[0]['rank']);
        
        // Cleanup
        $this->queryDatabase('DELETE FROM ' . $this->getTableName('context') . ' WHERE key = ?', [$contextKey]);
    }

    /**
     * Clean up test data
     */
    protected function tearDown(): void
    {
        // Remove any leftover test contexts
        $this->queryDatabase('DELETE FROM ' . $this->getTableName('context') . ' WHERE key LIKE ?', ['integtest_%']);
        
        parent::tearDown();
    }
}