<?php

namespace MODX\CLI\Tests\Integration\Commands\Template;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;

/**
 * Integration test for template:list command
 */
class TemplateListTest extends BaseIntegrationTest
{
    /**
     * Test that template:list executes successfully
     */
    public function testTemplateListExecutesSuccessfully()
    {
        $templateName = 'IntegrationTestTemplate_' . uniqid();
        
        // Create a test template first
        $this->executeCommandSuccessfully([
            'template:create',
            $templateName,
            '--content=<html><body>Test</body></html>'
        ]);
        
        // List templates
        $process = $this->executeCommandSuccessfully([
            'template:list'
        ]);
        
        $output = $process->getOutput();
        $this->assertStringContainsString($templateName, $output);
        
        // Cleanup
        $this->queryDatabase('DELETE FROM ' . $this->templatesTable . ' WHERE templatename = ?', [$templateName]);
    }

    /**
     * Test template:list with JSON output
     */
    public function testTemplateListReturnsValidJson()
    {
        // List with JSON
        $data = $this->executeCommandJson([
            'template:list'
        ]);
        
        $this->assertIsArray($data);
        $this->assertArrayHasKey('total', $data, 'JSON should have total key');
        $this->assertArrayHasKey('results', $data, 'JSON should have results key');
        $this->assertIsArray($data['results'], 'Results should be an array');
        
        // If results exist, verify structure
        if (!empty($data['results'])) {
            $firstTemplate = $data['results'][0];
            $this->assertArrayHasKey('templatename', $firstTemplate, 'Template should have templatename');
        }
    }

    /**
     * Test template:list filtering by category
     */
    public function testTemplateListFilterByCategory()
    {
        $categoryName = 'IntegrationTestCategory_' . uniqid();
        $templateName = 'IntegrationTestTemplate_' . uniqid();
        
        // Create category
        $this->executeCommandSuccessfully([
            'category:create',
            $categoryName
        ]);
        
        // Get category ID
        $catRows = $this->queryDatabase('SELECT id FROM ' . $this->categoriesTable . ' WHERE category = ?', [$categoryName]);
        $categoryId = $catRows[0]['id'];
        
        // Create template in category
        $this->executeCommandSuccessfully([
            'template:create',
            $templateName,
            '--category=' . $categoryId
        ]);
        
        // List templates with category filter
        $process = $this->executeCommandSuccessfully([
            'template:list',
            '--category=' . $categoryId
        ]);
        
        $output = $process->getOutput();
        $this->assertStringContainsString($templateName, $output);
        
        // Cleanup
        $this->queryDatabase('DELETE FROM ' . $this->templatesTable . ' WHERE templatename = ?', [$templateName]);
        $this->queryDatabase('DELETE FROM ' . $this->categoriesTable . ' WHERE id = ?', [$categoryId]);
    }

    /**
     * Test empty template list
     */
    public function testTemplateListWhenEmpty()
    {
        // Remove all test templates first
        $this->queryDatabase('DELETE FROM ' . $this->templatesTable . ' WHERE templatename LIKE ?', ['IntegrationTestTemplate_%']);
        
        $process = $this->executeCommandSuccessfully([
            'template:list'
        ]);
        
        $output = $process->getOutput();
        $this->assertNotEmpty($output);
    }

    /**
     * Test template:list with limit
     */
    public function testTemplateListWithLimit()
    {
        // Create multiple test templates
        $templateNames = [];
        for ($i = 0; $i < 3; $i++) {
            $templateName = 'IntegrationTestTemplate_' . uniqid() . '_' . $i;
            $templateNames[] = $templateName;
            
            $this->executeCommandSuccessfully([
                'template:create',
                $templateName
            ]);
        }
        
        // List with limit
        $data = $this->executeCommandJson([
            'template:list',
            '--limit=2'
        ]);
        
        $this->assertIsArray($data);
        $this->assertArrayHasKey('results', $data);
        
        // Cleanup
        foreach ($templateNames as $name) {
            $this->queryDatabase('DELETE FROM ' . $this->templatesTable . ' WHERE templatename = ?', [$name]);
        }
    }

    /**
     * Clean up test data
     */
    protected function tearDown(): void
    {
        $this->queryDatabase('DELETE FROM ' . $this->templatesTable . ' WHERE templatename LIKE ?', ['IntegrationTestTemplate_%']);
        $this->queryDatabase('DELETE FROM ' . $this->categoriesTable . ' WHERE category LIKE ?', ['IntegrationTestCategory_%']);
        parent::tearDown();
    }
}
