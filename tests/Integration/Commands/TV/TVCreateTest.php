<?php

namespace MODX\CLI\Tests\Integration\Commands\TV;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;

/**
 * Integration test for tv:create command
 */
class TVCreateTest extends BaseIntegrationTest
{
    /**
     * Test that tv:create executes successfully
     */
    public function testTVCreateExecutesSuccessfully()
    {
        $tvName = 'IntegrationTestTV_' . uniqid();
        
        $process = $this->executeCommandSuccessfully([
            'tv:create',
            $tvName,
            '--type=text',
            '--caption=Test TV'
        ]);
        
        $output = $process->getOutput();
        $this->assertStringContainsString('created successfully', $output);
        
        // Cleanup
        $this->queryDatabase('DELETE FROM ' . $this->tvsTable . ' WHERE name = ?', [$tvName]);
    }

    /**
     * Test TV creation with JSON output
     */
    public function testTVCreateReturnsValidJson()
    {
        $tvName = 'IntegrationTestTV_' . uniqid();
        
        $data = $this->executeCommandJson([
            'tv:create',
            $tvName,
            '--type=text',
            '--caption=Test TV'
        ]);
        
        $this->assertIsArray($data);
        $this->assertArrayHasKey('success', $data);
        $this->assertTrue($data['success']);
        
        // Cleanup
        $this->queryDatabase('DELETE FROM ' . $this->tvsTable . ' WHERE name = ?', [$tvName]);
    }

    /**
     * Test that created TV appears in database
     */
    public function testTVCreationPersistsToDatabase()
    {
        $tvName = 'IntegrationTestTV_' . uniqid();
        $caption = 'Integration Test Template Variable';
        
        $beforeCount = $this->countTableRows($this->tvsTable, 'name = ?', [$tvName]);
        $this->assertEquals(0, $beforeCount);
        
        $this->executeCommandSuccessfully([
            'tv:create',
            $tvName,
            '--type=text',
            '--caption=' . $caption
        ]);
        
        $afterCount = $this->countTableRows($this->tvsTable, 'name = ?', [$tvName]);
        $this->assertEquals(1, $afterCount);
        
        // Verify TV data
        $rows = $this->queryDatabase('SELECT caption, type FROM ' . $this->tvsTable . ' WHERE name = ?', [$tvName]);
        $this->assertEquals($caption, $rows[0]['caption']);
        $this->assertEquals('text', $rows[0]['type']);
        
        // Cleanup
        $this->queryDatabase('DELETE FROM ' . $this->tvsTable . ' WHERE name = ?', [$tvName]);
    }

    /**
     * Test TV creation with category
     */
    public function testTVCreationWithCategory()
    {
        $categoryName = 'IntegrationTestCategory_' . uniqid();
        $tvName = 'IntegrationTestTV_' . uniqid();
        
        // Create category first
        $this->executeCommandSuccessfully([
            'category:create',
            $categoryName
        ]);
        
        // Get category ID
        $catRows = $this->queryDatabase('SELECT id FROM ' . $this->categoriesTable . ' WHERE category = ?', [$categoryName]);
        $categoryId = $catRows[0]['id'];
        
        // Create TV with category
        $this->executeCommandSuccessfully([
            'tv:create',
            $tvName,
            '--type=text',
            '--category=' . $categoryId
        ]);
        
        // Verify TV has correct category
        $tvRows = $this->queryDatabase('SELECT category FROM ' . $this->tvsTable . ' WHERE name = ?', [$tvName]);
        $this->assertEquals($categoryId, $tvRows[0]['category']);
        
        // Cleanup
        $this->queryDatabase('DELETE FROM ' . $this->tvsTable . ' WHERE name = ?', [$tvName]);
        $this->queryDatabase('DELETE FROM ' . $this->categoriesTable . ' WHERE id = ?', [$categoryId]);
    }

    /**
     * Test TV creation with default value
     */
    public function testTVCreationWithDefaultValue()
    {
        $tvName = 'IntegrationTestTV_' . uniqid();
        $defaultValue = 'Default Test Value';
        
        $this->executeCommandSuccessfully([
            'tv:create',
            $tvName,
            '--type=text',
            '--default_text=' . $defaultValue
        ]);
        
        // Verify default value in database
        $rows = $this->queryDatabase('SELECT default_text FROM ' . $this->tvsTable . ' WHERE name = ?', [$tvName]);
        $this->assertEquals($defaultValue, $rows[0]['default_text']);
        
        // Cleanup
        $this->queryDatabase('DELETE FROM ' . $this->tvsTable . ' WHERE name = ?', [$tvName]);
    }

    /**
     * Test TV creation with different types
     */
    public function testTVCreationWithDifferentTypes()
    {
        $types = ['text', 'textarea', 'number', 'date', 'checkbox'];
        
        foreach ($types as $type) {
            $tvName = 'IntegrationTestTV_' . $type . '_' . uniqid();
            
            $this->executeCommandSuccessfully([
                'tv:create',
                $tvName,
                '--type=' . $type
            ]);
            
            // Verify type in database
            $rows = $this->queryDatabase('SELECT type FROM ' . $this->tvsTable . ' WHERE name = ?', [$tvName]);
            $this->assertEquals($type, $rows[0]['type']);
            
            // Cleanup
            $this->queryDatabase('DELETE FROM ' . $this->tvsTable . ' WHERE name = ?', [$tvName]);
        }
    }

    /**
     * Test error handling for duplicate TV name
     */
    public function testTVCreationWithDuplicateName()
    {
        $tvName = 'IntegrationTestTV_' . uniqid();
        
        // Create first TV
        $this->executeCommandSuccessfully([
            'tv:create',
            $tvName,
            '--type=text'
        ]);
        
        // Try to create duplicate
        $process = $this->executeCommand([
            'tv:create',
            $tvName,
            '--type=text'
        ]);
        
        $output = $process->getOutput();
        $this->assertNotEmpty($output);
        
        // Cleanup
        $this->queryDatabase('DELETE FROM ' . $this->tvsTable . ' WHERE name = ?', [$tvName]);
    }

    /**
     * Clean up test data
     */
    protected function tearDown(): void
    {
        // Remove any leftover test TVs
        $this->queryDatabase('DELETE FROM ' . $this->tvsTable . ' WHERE name LIKE ?', ['IntegrationTestTV_%']);
        $this->queryDatabase('DELETE FROM ' . $this->categoriesTable . ' WHERE category LIKE ?', ['IntegrationTestCategory_%']);
        
        parent::tearDown();
    }
}
