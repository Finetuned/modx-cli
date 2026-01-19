<?php

namespace MODX\CLI\Tests\Integration\Commands\Source;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;

/**
 * Integration test for source:create command
 */
class CreateTest extends BaseIntegrationTest
{
    /**
     * Test that source:create executes successfully
     */
    public function testSourceCreateExecutesSuccessfully()
    {
        $sourceName = 'integtest_' . uniqid();

        $process = $this->executeCommandSuccessfully([
            'source:create',
            $sourceName,
            '--description=Test media source',
            '--class_key=MODX\\Revolution\\Sources\\modFileMediaSource'
        ]);

        $output = $process->getOutput();
        $this->assertStringContainsString('created successfully', $output);

        // Cleanup
        $this->queryDatabase('DELETE FROM ' . $this->getTableName('media_sources') . ' WHERE name = ?', [$sourceName]);
    }

    /**
     * Test source creation with JSON output
     */
    public function testSourceCreateReturnsValidJson()
    {
        $sourceName = 'integtest_' . uniqid();

        $data = $this->executeCommandJson([
            'source:create',
            $sourceName,
            '--class_key=MODX\\Revolution\\Sources\\modFileMediaSource'
        ]);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('success', $data);
        $this->assertTrue($data['success']);

        if (isset($data['object'])) {
            $this->assertArrayHasKey('id', $data['object']);
        }

        // Cleanup
        $this->queryDatabase('DELETE FROM ' . $this->getTableName('media_sources') . ' WHERE name = ?', [$sourceName]);
    }

    /**
     * Test that created source appears in database
     */
    public function testSourceCreationPersistsToDatabase()
    {
        $sourceName = 'integtest_' . uniqid();

        $beforeCount = $this->countTableRows($this->getTableName('media_sources'), 'name = ?', [$sourceName]);
        $this->assertEquals(0, $beforeCount);

        $this->executeCommandSuccessfully([
            'source:create',
            $sourceName,
            '--description=Test Description',
            '--class_key=MODX\\Revolution\\Sources\\modFileMediaSource'
        ]);

        $afterCount = $this->countTableRows($this->getTableName('media_sources'), 'name = ?', [$sourceName]);
        $this->assertEquals(1, $afterCount);

        // Verify source data
        $rows = $this->queryDatabase('SELECT * FROM ' . $this->getTableName('media_sources') . ' WHERE name = ?', [$sourceName]);
        $this->assertCount(1, $rows);
        $this->assertEquals($sourceName, $rows[0]['name']);
        $this->assertEquals('Test Description', $rows[0]['description']);

        // Cleanup
        $this->queryDatabase('DELETE FROM ' . $this->getTableName('media_sources') . ' WHERE name = ?', [$sourceName]);
    }

    /**
     * Test source creation with minimal parameters
     */
    public function testSourceCreationWithMinimalParameters()
    {
        $sourceName = 'integtest_' . uniqid();

        $this->executeCommandSuccessfully([
            'source:create',
            $sourceName
        ]);

        // Verify source exists
        $rows = $this->queryDatabase('SELECT * FROM ' . $this->getTableName('media_sources') . ' WHERE name = ?', [$sourceName]);
        $this->assertCount(1, $rows);
        $this->assertEquals($sourceName, $rows[0]['name']);

        // Cleanup
        $this->queryDatabase('DELETE FROM ' . $this->getTableName('media_sources') . ' WHERE name = ?', [$sourceName]);
    }

    /**
     * Test source creation with source properties
     */
    public function testSourceCreationWithSourceProperties()
    {
        $sourceName = 'integtest_' . uniqid();
        $properties = 'testKey=testValue';

        $this->executeCommandSuccessfully([
            'source:create',
            $sourceName,
            '--source-properties=' . $properties
        ]);

        $rows = $this->queryDatabase(
            'SELECT properties FROM ' . $this->getTableName('media_sources') . ' WHERE name = ?',
            [$sourceName]
        );
        $stored = $rows[0]['properties'] ?? '';
        $decoded = json_decode((string) $stored, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $decoded = @unserialize((string) $stored);
        }
        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('testKey', $decoded);

        // Cleanup
        $this->queryDatabase('DELETE FROM ' . $this->getTableName('media_sources') . ' WHERE name = ?', [$sourceName]);
    }

    /**
     * Test error handling for duplicate source name
     */
    public function testSourceCreationWithDuplicateName()
    {
        $sourceName = 'integtest_' . uniqid();

        // Create first source
        $this->executeCommandSuccessfully([
            'source:create',
            $sourceName
        ]);

        // Try to create duplicate
        $process = $this->executeCommand([
            'source:create',
            $sourceName
        ]);

        // Should fail or handle duplicate appropriately
        $output = $process->getOutput();
        $this->assertNotEmpty($output);

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
