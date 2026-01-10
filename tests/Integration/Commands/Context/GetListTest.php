<?php

namespace MODX\CLI\Tests\Integration\Commands\Context;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;

/**
 * Integration test for context:list command
 */
class GetListTest extends BaseIntegrationTest
{
    /**
     * Test that context:list executes successfully
     */
    public function testContextListExecutesSuccessfully()
    {
        $process = $this->executeCommandSuccessfully([
            'context:list'
        ]);
        
        $output = $process->getOutput();
        $this->assertNotEmpty($output);
        // Default 'web' context should exist
        $this->assertStringContainsString('web', $output);
    }

    /**
     * Test context:list with JSON output
     */
    public function testContextListReturnsValidJson()
    {
        $data = $this->executeCommandJson([
            'context:list'
        ]);
        
        $this->assertIsArray($data);
        $this->assertArrayHasKey('total', $data);
        $this->assertArrayHasKey('results', $data);
        $this->assertIsArray($data['results']);
    }

    /**
     * Test context:list shows created contexts
     */
    public function testContextListShowsCreatedContexts()
    {
        $contextKey = 'integtest-' . uniqid();
        
        // Create test context
        $this->queryDatabase(
            'INSERT INTO ' . $this->getTableName('context') . ' (`key`, name, description, rank) VALUES (?, ?, ?, ?)',
            [$contextKey, 'Test Context', 'Test Description', 0]
        );
        
        $data = $this->executeCommandJson([
            'context:list'
        ]);
        
        // Find our test context in results
        $found = false;
        foreach ($data['results'] as $context) {
            if ($context['key'] === $contextKey) {
                $found = true;
                break;
            }
        }
        
        $this->assertTrue($found, 'Created context should appear in list');
        
        // Cleanup
        $this->queryDatabase('DELETE FROM ' . $this->getTableName('context') . ' WHERE `key` = ?', [$contextKey]);
    }

    /**
     * Test context:list with limit parameter
     */
    public function testContextListWithLimit()
    {
        $data = $this->executeCommandJson([
            'context:list',
            '--limit=2'
        ]);
        
        $this->assertIsArray($data);
        $this->assertArrayHasKey('results', $data);
        $this->assertLessThanOrEqual(2, count($data['results']));
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
