<?php

namespace MODX\CLI\Tests\Integration\Commands\Category;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;

/**
 * Integration test for category:get command
 */
class CategoryGetTest extends BaseIntegrationTest
{
    /**
     * Test that category:get retrieves existing category
     */
    public function testCategoryGetExecutesSuccessfully()
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
        
        // Get category
        $process = $this->executeCommandSuccessfully([
            'category:get',
            $categoryId
        ]);
        
        $output = $process->getOutput();
        $this->assertStringContainsString($categoryName, $output);
        
        // Cleanup
        $this->queryDatabase('DELETE FROM modx_categories WHERE id = ?', [$categoryId]);
    }

    /**
     * Test category:get with JSON output
     */
    public function testCategoryGetReturnsValidJson()
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
        
        // Get category with JSON
        $data = $this->executeCommandJson([
            'category:get',
            $categoryId
        ]);
        
        $this->assertIsArray($data);
        $this->assertArrayHasKey('id', $data);
        $this->assertEquals($categoryId, $data['id']);
        $this->assertEquals($categoryName, $data['category']);
        
        // Cleanup
        $this->queryDatabase('DELETE FROM modx_categories WHERE id = ?', [$categoryId]);
    }

    /**
     * Test error handling for non-existent category
     */
    public function testCategoryGetWithInvalidId()
    {
        $process = $this->executeCommand([
            'category:get',
            '999999'
        ]);
        
        $output = $process->getOutput();
        $this->assertNotEmpty($output);
    }

    /**
     * Clean up test data
     */
    protected function tearDown(): void
    {
        $this->queryDatabase('DELETE FROM modx_categories WHERE category LIKE ?', ['IntegrationTestCategory_%']);
        parent::tearDown();
    }
}
