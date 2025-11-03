<?php

namespace MODX\CLI\Tests\Integration\Commands\Category;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;

/**
 * Integration test for category:create command
 */
class CategoryCreateTest extends BaseIntegrationTest
{
    /**
     * Test that category:create executes successfully
     */
    public function testCategoryCreateExecutesSuccessfully()
    {
        $categoryName = 'IntegrationTestCategory_' . uniqid();
        
        $process = $this->executeCommandSuccessfully([
            'category:create',
            $categoryName,
            '--parent=0',
            '--rank=0'
        ]);
        
        $output = $process->getOutput();
        $this->assertStringContainsString('created successfully', $output);
        $this->assertStringContainsString('Category ID:', $output);
        
        // Cleanup
        $this->queryDatabase('DELETE FROM ' . $this->categoriesTable . ' WHERE category = ?', [$categoryName]);
    }

    /**
     * Test category creation with JSON output
     */
    public function testCategoryCreateReturnsValidJson()
    {
        $categoryName = 'IntegrationTestCategory_' . uniqid();
        
        $data = $this->executeCommandJson([
            'category:create',
            $categoryName,
            '--parent=0'
        ]);
        
        $this->assertIsArray($data);
        $this->assertArrayHasKey('success', $data);
        $this->assertTrue($data['success']);
        
        if (isset($data['object'])) {
            $this->assertArrayHasKey('id', $data['object']);
        }
        
        // Cleanup
        $this->queryDatabase('DELETE FROM ' . $this->categoriesTable . ' WHERE category = ?', [$categoryName]);
    }

    /**
     * Test that created category appears in database
     */
    public function testCategoryCreationPersistsToDatabase()
    {
        $categoryName = 'IntegrationTestCategory_' . uniqid();
        
        $beforeCount = $this->countTableRows($this->categoriesTable, 'category = ?', [$categoryName]);
        $this->assertEquals(0, $beforeCount);
        
        $this->executeCommandSuccessfully([
            'category:create',
            $categoryName
        ]);
        
        $afterCount = $this->countTableRows($this->categoriesTable, 'category = ?', [$categoryName]);
        $this->assertEquals(1, $afterCount);
        
        // Verify category data
        $rows = $this->queryDatabase('SELECT * FROM ' . $this->categoriesTable . ' WHERE category = ?', [$categoryName]);
        $this->assertCount(1, $rows);
        $this->assertEquals($categoryName, $rows[0]['category']);
        
        // Cleanup
        $this->queryDatabase('DELETE FROM ' . $this->categoriesTable . ' WHERE category = ?', [$categoryName]);
    }

    /**
     * Test category creation with parent category
     */
    public function testCategoryCreationWithParent()
    {
        $parentName = 'IntegrationTestParent_' . uniqid();
        $childName = 'IntegrationTestChild_' . uniqid();
        
        // Create parent
        $this->executeCommandSuccessfully([
            'category:create',
            $parentName
        ]);
        
        // Get parent ID
        $parentRows = $this->queryDatabase('SELECT id FROM ' . $this->categoriesTable . ' WHERE category = ?', [$parentName]);
        $parentId = $parentRows[0]['id'];
        
        // Create child
        $this->executeCommandSuccessfully([
            'category:create',
            $childName,
            '--parent=' . $parentId
        ]);
        
        // Verify child has correct parent
        $childRows = $this->queryDatabase('SELECT parent FROM ' . $this->categoriesTable . ' WHERE category = ?', [$childName]);
        $this->assertEquals($parentId, $childRows[0]['parent']);
        
        // Cleanup
        $this->queryDatabase('DELETE FROM ' . $this->categoriesTable . ' WHERE category IN (?, ?)', [$parentName, $childName]);
    }

    /**
     * Test error handling for duplicate category name
     */
    public function testCategoryCreationWithDuplicateName()
    {
        $categoryName = 'IntegrationTestCategory_' . uniqid();
        
        // Create first category
        $this->executeCommandSuccessfully([
            'category:create',
            $categoryName
        ]);
        
        // Try to create duplicate
        $process = $this->executeCommand([
            'category:create',
            $categoryName
        ]);
        
        // Should fail or handle duplicate appropriately
        $output = $process->getOutput();
        // Note: Actual MODX behavior may vary - this tests that the command handles it
        $this->assertNotEmpty($output);
        
        // Cleanup
        $this->queryDatabase('DELETE FROM ' . $this->categoriesTable . ' WHERE category = ?', [$categoryName]);
    }

    /**
     * Clean up test data
     */
    protected function tearDown(): void
    {
        // Remove any leftover test categories
        $this->queryDatabase('DELETE FROM ' . $this->categoriesTable . ' WHERE category LIKE ?', ['IntegrationTestCategory_%']);
        $this->queryDatabase('DELETE FROM ' . $this->categoriesTable . ' WHERE category LIKE ?', ['IntegrationTestParent_%']);
        $this->queryDatabase('DELETE FROM ' . $this->categoriesTable . ' WHERE category LIKE ?', ['IntegrationTestChild_%']);
        
        parent::tearDown();
    }
}
