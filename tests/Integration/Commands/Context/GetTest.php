<?php

namespace MODX\CLI\Tests\Integration\Commands\Context;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;

/**
 * Integration test for context:get command
 */
class GetTest extends BaseIntegrationTest
{
    /**
     * Test that context:get executes successfully
     */
    public function testContextGetExecutesSuccessfully()
    {
        $contextKey = 'integtest-' . uniqid();

        // Create test context
        $this->queryDatabase(
            'INSERT INTO ' . $this->getTableName('context') . ' (`key`, name, description, rank) VALUES (?, ?, ?, ?)',
            [$contextKey, 'Test Context', 'Test Description', 0]
        );

        $process = $this->executeCommandSuccessfully([
            'context:get',
            $contextKey
        ]);

        $output = $process->getOutput();
        $this->assertStringContainsString($contextKey, $output);
        $this->assertStringContainsString('Test Context', $output);

        // Cleanup
        $this->queryDatabase('DELETE FROM ' . $this->getTableName('context') . ' WHERE `key` = ?', [$contextKey]);
    }

    /**
     * Test context:get with JSON output
     */
    public function testContextGetReturnsValidJson()
    {
        $contextKey = 'integtest-' . uniqid();

        // Create test context
        $this->queryDatabase(
            'INSERT INTO ' . $this->getTableName('context') . ' (`key`, name, description, rank) VALUES (?, ?, ?, ?)',
            [$contextKey, 'Test Context', 'Test Description', 0]
        );

        $data = $this->executeCommandJson([
            'context:get',
            $contextKey
        ]);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('success', $data);
        $this->assertTrue($data['success']);

        if (isset($data['object'])) {
            $this->assertEquals($contextKey, $data['object']['key']);
            $this->assertEquals('Test Context', $data['object']['name']);
        }

        // Cleanup
        $this->queryDatabase('DELETE FROM ' . $this->getTableName('context') . ' WHERE `key` = ?', [$contextKey]);
    }

    /**
     * Test context:get with non-existent context
     */
    public function testContextGetWithNonExistentContext()
    {
        $process = $this->executeCommand([
            'context:get',
            'nonexistent_context_' . uniqid()
        ]);

        $output = $process->getOutput();
        $this->assertNotEmpty($output);
    }

    /**
     * Test context:get retrieves correct data
     */
    public function testContextGetRetrievesCorrectData()
    {
        $contextKey = 'integtest-' . uniqid();

        // Create test context with specific values
        $this->queryDatabase(
            'INSERT INTO ' . $this->getTableName('context') . ' (`key`, name, description, rank) VALUES (?, ?, ?, ?)',
            [$contextKey, 'Specific Name', 'Specific Description', 5]
        );

        $data = $this->executeCommandJson([
            'context:get',
            $contextKey
        ]);

        $this->assertTrue($data['success']);
        $this->assertEquals('Specific Name', $data['object']['name']);
        $this->assertEquals('Specific Description', $data['object']['description']);
        $this->assertEquals(5, $data['object']['rank']);

        // Cleanup
        $this->queryDatabase('DELETE FROM ' . $this->getTableName('context') . ' WHERE `key` = ?', [$contextKey]);
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
