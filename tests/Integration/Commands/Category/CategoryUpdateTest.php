<?php

namespace MODX\CLI\Tests\Integration\Commands\Category;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;

/**
 * Integration test for category:update command
 */
class CategoryUpdateTest extends BaseIntegrationTest
{
    /**
     * Test that category:update executes successfully
     */
    public function testCategoryUpdateExecutesSuccessfully()
    {
        $originalName = 'IntegrationTestCategory_' . uniqid();
        $updatedName = 'IntegrationTestCategoryUpdated_' . uniqid();
        
        // Create category
        $this->executeCommandSuccessfully([
            'category:create',
            $originalName
        ]);
        
        // Get category ID
        $rows = $this->queryDatabase('SELECT id FROM ' . $this->categoriesTable . ' WHERE category = ?', [$originalName]);
        $categoryId = $rows[0]['id'];
        
        // Update category
        $process = $this->executeCommandSuccessfully([
            'category:update',
            $categoryId,
            '--category=' . $updatedName
        ]);
        
        $output = $process->getOutput();
        $this->assertStringContainsString('updated successfully', $output);
        
        // Verify update in database
        $afterRows = $this->queryDatabase('SELECT category FROM ' . $this->categoriesTable . ' WHERE id = ?', [$categoryId]);
        $this->assertEquals($updatedName, $afterRows[0]['category']);
        
        // Cleanup
        $this->queryDatabase('DELETE FROM ' . $this->categoriesTable . ' WHERE id = ?', [$categoryId]);
    }

    /**
     * Test category update with JSON output
     */
    public function testCategoryUpdateReturnsValidJson()
    {
        $originalName = 'IntegrationTestCategory_' . uniqid();
        $updatedName = 'IntegrationTestCategoryUpdated_' . uniqid();
        
        // Create category
        $this->executeCommandSuccessfully([
            'category:create',
            $originalName
        ]);
        
        // Get category ID
        $rows = $this->queryDatabase('SELECT id FROM ' . $this->categoriesTable . ' WHERE category = ?', [$originalName]);
        $categoryId = $rows[0]['id'];
        
        // Update with JSON
        $data = $this->executeCommandJson([
            'category:update',
            $categoryId,
            '--category=' . $updatedName
        ]);
        
        $this->assertIsArray($data);
        $this->assertArrayHasKey('success', $data);
        $this->assertTrue($data['success']);
        
        // Cleanup
        $this->queryDatabase('DELETE FROM ' . $this->categoriesTable . ' WHERE id = ?', [$categoryId]);
    }

    /**
     * Test updating category parent
     */
    public function testCategoryUpdateParent()
    {
        $parentName = 'IntegrationTestParent_' . uniqid();
        $categoryName = 'IntegrationTestCategory_' . uniqid();
        
        // Create parent and category
        $this->executeCommandSuccessfully(['category:create', $parentName]);
        $this->executeCommandSuccessfully(['category:create', $categoryName]);
        
        // Get IDs
        $parentRows = $this->queryDatabase('SELECT id FROM ' . $this->categoriesTable . ' WHERE category = ?', [$parentName]);
        $parentId = $parentRows[0]['id'];
        
        $categoryRows = $this->queryDatabase('SELECT id FROM ' . $this->categoriesTable . ' WHERE category = ?', [$categoryName]);
        $categoryId = $categoryRows[0]['id'];
        
        // Update parent
        $this->executeCommandSuccessfully([
            'category:update',
            $categoryId,
            '--parent=' . $parentId
        ]);
        
        // Verify parent in database
        $afterRows = $this->queryDatabase('SELECT parent FROM ' . $this->categoriesTable . ' WHERE id = ?', [$categoryId]);
        $this->assertEquals($parentId, $afterRows[0]['parent']);
        
        // Cleanup
        $this->queryDatabase('DELETE FROM ' . $this->categoriesTable . ' WHERE id IN (?, ?)', [$parentId, $categoryId]);
    }

    /**
     * Test error handling for non-existent category
     */
    public function testCategoryUpdateWithInvalidId()
    {
        $process = $this->executeCommand([
            'category:update',
            '999999',
            '--category=Test'
        ]);
        
        $exitCode = $process->getExitCode();
        
        // The command should return non-zero exit code for error
        $this->assertEquals(1, $exitCode, 'Command should return exit code 1 for invalid category ID');
        
        // Should fail or return error
       // $output = $process->getOutput();
       // $this->assertNotEmpty($output);
        // Note: Specific error behavior depends on MODX implementation
    }

    /**
     * Test updating multiple fields at once
     */
    public function testCategoryUpdateMultipleFields()
    {
        $originalName = 'IntegrationTestCategory_' . uniqid();
        $updatedName = 'IntegrationTestCategoryUpdated_' . uniqid();
        
        // Create category
        $this->executeCommandSuccessfully([
            'category:create',
            $originalName,
            '--rank=0'
        ]);
        
        // Get category ID
        $rows = $this->queryDatabase('SELECT id FROM ' . $this->categoriesTable . ' WHERE category = ?', [$originalName]);
        $categoryId = $rows[0]['id'];
        
        // Update multiple fields
        $this->executeCommandSuccessfully([
            'category:update',
            $categoryId,
            '--category=' . $updatedName,
            '--rank=5'
        ]);
        
        // Verify updates in database
        $afterRows = $this->queryDatabase('SELECT category, rank FROM ' . $this->categoriesTable . ' WHERE id = ?', [$categoryId]);
        $this->assertEquals($updatedName, $afterRows[0]['category']);
        $this->assertEquals(5, $afterRows[0]['rank']);
        
        // Cleanup
        $this->queryDatabase('DELETE FROM ' . $this->categoriesTable . ' WHERE id = ?', [$categoryId]);
    }

    /**
     * Clean up test data
     */
    protected function tearDown(): void
    {
        // Remove any leftover test categories
        $this->queryDatabase('DELETE FROM ' . $this->categoriesTable . ' WHERE category LIKE ?', ['IntegrationTestCategory_%']);
        $this->queryDatabase('DELETE FROM ' . $this->categoriesTable . ' WHERE category LIKE ?', ['IntegrationTestCategoryUpdated_%']);
        $this->queryDatabase('DELETE FROM ' . $this->categoriesTable . ' WHERE category LIKE ?', ['IntegrationTestParent_%']);
        
        parent::tearDown();
    }
}
