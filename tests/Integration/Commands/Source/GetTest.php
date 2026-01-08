<?php

namespace MODX\CLI\Tests\Integration\Commands\Source;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;

/**
 * Integration test for source:get command
 */
class GetTest extends BaseIntegrationTest
{
    /**
     * Test that source:get executes successfully
     */
    public function testSourceGetExecutesSuccessfully()
    {
        $sourceName = 'integtest_' . uniqid();
        
        // Create test source
        $this->queryDatabase(
            'INSERT INTO ' . $this->getTableName('media_sources') . ' (name, description, class_key) VALUES (?, ?, ?)',
            [$sourceName, 'Test Description', 'MODX\\Revolution\\Sources\\modFileMediaSource']
        );
        
        $sourceId = $this->queryDatabase('SELECT id FROM ' . $this->getTableName('media_sources') . ' WHERE name = ?', [$sourceName])[0]['id'];
        
        $process = $this->executeCommandSuccessfully([
            'source:get',
            $sourceId
        ]);
        
        $output = $process->getOutput();
        $this->assertStringContainsString($sourceName, $output);
        
        // Cleanup
        $this->queryDatabase('DELETE FROM ' . $this->getTableName('media_sources') . ' WHERE name = ?', [$sourceName]);
    }

    /**
     * Test source:get with JSON output
     */
    public function testSourceGetReturnsValidJson()
    {
        $sourceName = 'integtest_' . uniqid();
        
        // Create test source
        $this->queryDatabase(
            'INSERT INTO ' . $this->getTableName('media_sources') . ' (name, description, class_key) VALUES (?, ?, ?)',
            [$sourceName, 'Test Description', 'MODX\\Revolution\\Sources\\modFileMediaSource']
        );
        
        $sourceId = $this->queryDatabase('SELECT id FROM ' . $this->getTableName('media_sources') . ' WHERE name = ?', [$sourceName])[0]['id'];
        
        $data = $this->executeCommandJson([
            'source:get',
            $sourceId
        ]);
        
        $this->assertIsArray($data);
        $this->assertArrayHasKey('success', $data);
        $this->assertTrue($data['success']);
        
        if (isset($data['object'])) {
            $this->assertEquals($sourceName, $data['object']['name']);
        }
        
        // Cleanup
        $this->queryDatabase('DELETE FROM ' . $this->getTableName('media_sources') . ' WHERE name = ?', [$sourceName]);
    }

    /**
     * Test source:get with non-existent source
     */
    public function testSourceGetWithNonExistentSource()
    {
        $process = $this->executeCommand([
            'source:get',
            '999999'
        ]);
        
        $output = $process->getOutput();
        $this->assertNotEmpty($output);
    }

    /**
     * Test source:get retrieves correct data
     */
    public function testSourceGetRetrievesCorrectData()
    {
        $sourceName = 'integtest_' . uniqid();
        
        // Create test source with specific values
        $this->queryDatabase(
            'INSERT INTO ' . $this->getTableName('media_sources') . ' (name, description, class_key) VALUES (?, ?, ?)',
            [$sourceName, 'Specific Description', 'MODX\\Revolution\\Sources\\modFileMediaSource']
        );
        
        $sourceId = $this->queryDatabase('SELECT id FROM ' . $this->getTableName('media_sources') . ' WHERE name = ?', [$sourceName])[0]['id'];
        
        $data = $this->executeCommandJson([
            'source:get',
            $sourceId
        ]);
        
        $this->assertTrue($data['success']);
        $this->assertEquals('Specific Description', $data['object']['description']);
        
        // Cleanup
        $this->queryDatabase('DELETE FROM ' . $this->getTableName('media_sources') . ' WHERE name = ?', [$sourceName]);
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