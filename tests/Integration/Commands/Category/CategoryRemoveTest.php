<?php

namespace MODX\CLI\Tests\Integration\Commands\Category;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;

/**
 * Integration test for category:remove command
 */
class CategoryRemoveTest extends BaseIntegrationTest
{
    /**
     * Test that category:remove deletes a category
     */
    public function testCategoryRemoveExecutesSuccessfully()
    {
        $categoryName = 'IntegrationTestCategory_' . uniqid();
        
        // Create category first
        $this->executeCommandSuccessfully([
            'category:create',
            $categoryName
        ]);
        
        // Get the category ID
        $rows = $this->queryDatabase('SELECT id FROM modx_categories WHERE category = ?', [$categoryName]);
        $categoryId = $rows[0]['id'];
        
        // Verify category exists
        $beforeCount = $this->countTableRows('modx_categories', 'id = ?', [$categoryId]);
        $this->assertEquals(1, $beforeCount);
        
        // Remove category
        $process = $this->executeCommandSuccessfully([
            'category:remove',
            $categoryId
        ]);
        
        $output = $process->getOutput();
        $this->assertStringContainsString('removed successfully', $output);
        
        // Verify category no longer exists
        $afterCount = $this->countTableRows('modx_categories', 'id = ?', [$categoryId]);
        $this->assertEquals(0, $afterCount);
    }

    /**
     * Test category:remove with JSON output
     */
    public function testCategoryRemoveReturnsValidJson()
    {
        $categoryName = 'IntegrationTestCategory_' . uniqid();
        
        // Create category
        $this->executeCommandSuccessfully([
            'category:create',
            $categoryName
        ]);
        
        // Get category ID
        $rows = $this->queryDatabase('SELECT id FROM modx_categories WHERE category = ?', [$categoryName]);
        $categoryId = $rows[0]['id'];
        
        // Remove category with JSON
        $data = $this->executeCommandJson([
            'category:remove',
            $categoryId
        ]);
        
        $this->assertIsArray($data);
        $this->assertArrayHasKey('success', $data);
        $this->assertTrue($data['success']);
    }

    /**
     * Test error handling for non-existent category
     */
    public function testCategoryRemoveWithInvalidId()
    {
        $process = $this->executeCommand([
            'category:remove',
            '999999'
        ]);
        
        $output = $process->getOutput();
        $this->assertNotEmpty($output);
    }

    /**
     * Test removing category with children (should fail or require force)
     */
    public function testCategoryRemoveWithChildren()
    {
        $parentName = 'IntegrationTestParent_' . uniqid();
        $childName = 'IntegrationTestChild_' . uniqid();
        
        // Create parent category
        $this->executeCommandSuccessfully([
            'category:create',
            $parentName
        ]);
        
        // Get parent ID
        $parentRows = $this->queryDatabase('SELECT id FROM modx_categories WHERE category = ?', [$parentName]);
        $parentId = $parentRows[0]['id'];
        
        // Create child category
        $this->executeCommandSuccessfully([
            'category:create',
            $childName,
            '--parent=' . $parentId
        ]);
        
        // Try to remove parent (should handle gracefully)
        $process = $this->executeCommand([
            'category:remove',
            $parentId
        ]);
        
        $output = $process->getOutput();
        $this->assertNotEmpty($output);
        
        // Cleanup
        $this->queryDatabase('DELETE FROM modx_categories WHERE category LIKE ?', ['IntegrationTestChild_%']);
        $this->queryDatabase('DELETE FROM modx_categories WHERE category LIKE ?', ['IntegrationTestParent_%']);
    }

    /**
     * Clean up test data
     */
    protected function tearDown(): void
    {
        $this->queryDatabase('DELETE FROM modx_categories WHERE category LIKE ?', ['IntegrationTestCategory_%']);
        $this->queryDatabase('DELETE FROM modx_categories WHERE category LIKE ?', ['IntegrationTestChild_%']);
        $this->queryDatabase('DELETE FROM modx_categories WHERE category LIKE ?', ['IntegrationTestParent_%']);
        parent::tearDown();
    }
}
