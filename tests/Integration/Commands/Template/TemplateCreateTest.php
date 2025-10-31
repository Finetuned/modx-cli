<?php

namespace MODX\CLI\Tests\Integration\Commands\Template;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;

/**
 * Integration test for template:create command
 */
class TemplateCreateTest extends BaseIntegrationTest
{
    /**
     * Test that template:create executes successfully
     */
    public function testTemplateCreateExecutesSuccessfully()
    {
        $templateName = 'IntegrationTestTemplate_' . uniqid();
        
        $process = $this->executeCommandSuccessfully([
            'template:create',
            $templateName,
            '--category=0'
        ]);
        
        $output = $process->getOutput();
        $this->assertStringContainsString('created successfully', $output);
        
        // Cleanup
        $this->queryDatabase('DELETE FROM modx_site_templates WHERE templatename = ?', [$templateName]);
    }

    /**
     * Test template creation with JSON output
     */
    public function testTemplateCreateReturnsValidJson()
    {
        $templateName = 'IntegrationTestTemplate_' . uniqid();
        
        $data = $this->executeCommandJson([
            'template:create',
            $templateName,
            '--content=<!DOCTYPE html><html><body>[[*content]]</body></html>'
        ]);
        
        $this->assertIsArray($data);
        $this->assertArrayHasKey('success', $data);
        $this->assertTrue($data['success']);
        
        // Cleanup
        $this->queryDatabase('DELETE FROM modx_site_templates WHERE templatename = ?', [$templateName]);
    }

    /**
     * Test that created template appears in database
     */
    public function testTemplateCreationPersistsToDatabase()
    {
        $templateName = 'IntegrationTestTemplate_' . uniqid();
        $content = '<!DOCTYPE html><html><head><title>[[*pagetitle]]</title></head><body>[[*content]]</body></html>';
        
        $beforeCount = $this->countTableRows('modx_site_templates', 'templatename = ?', [$templateName]);
        $this->assertEquals(0, $beforeCount);
        
        $this->executeCommandSuccessfully([
            'template:create',
            $templateName,
            '--content=' . $content
        ]);
        
        $afterCount = $this->countTableRows('modx_site_templates', 'templatename = ?', [$templateName]);
        $this->assertEquals(1, $afterCount);
        
        // Verify template content
        $rows = $this->queryDatabase('SELECT content FROM modx_site_templates WHERE templatename = ?', [$templateName]);
        $this->assertEquals($content, $rows[0]['content']);
        
        // Cleanup
        $this->queryDatabase('DELETE FROM modx_site_templates WHERE templatename = ?', [$templateName]);
    }

    /**
     * Test template creation with category
     */
    public function testTemplateCreationWithCategory()
    {
        $categoryName = 'IntegrationTestCategory_' . uniqid();
        $templateName = 'IntegrationTestTemplate_' . uniqid();
        
        // Create category first
        $this->executeCommandSuccessfully([
            'category:create',
            $categoryName
        ]);
        
        // Get category ID
        $catRows = $this->queryDatabase('SELECT id FROM modx_categories WHERE category = ?', [$categoryName]);
        $categoryId = $catRows[0]['id'];
        
        // Create template with category
        $this->executeCommandSuccessfully([
            'template:create',
            $templateName,
            '--category=' . $categoryId
        ]);
        
        // Verify template has correct category
        $templateRows = $this->queryDatabase('SELECT category FROM modx_site_templates WHERE templatename = ?', [$templateName]);
        $this->assertEquals($categoryId, $templateRows[0]['category']);
        
        // Cleanup
        $this->queryDatabase('DELETE FROM modx_site_templates WHERE templatename = ?', [$templateName]);
        $this->queryDatabase('DELETE FROM modx_categories WHERE id = ?', [$categoryId]);
    }

    /**
     * Test template creation with description
     */
    public function testTemplateCreationWithDescription()
    {
        $templateName = 'IntegrationTestTemplate_' . uniqid();
        $description = 'Test template for integration testing';
        
        $this->executeCommandSuccessfully([
            'template:create',
            $templateName,
            '--description=' . $description
        ]);
        
        // Verify description in database
        $rows = $this->queryDatabase('SELECT description FROM modx_site_templates WHERE templatename = ?', [$templateName]);
        $this->assertEquals($description, $rows[0]['description']);
        
        // Cleanup
        $this->queryDatabase('DELETE FROM modx_site_templates WHERE templatename = ?', [$templateName]);
    }

    /**
     * Test error handling for duplicate template name
     */
    public function testTemplateCreationWithDuplicateName()
    {
        $templateName = 'IntegrationTestTemplate_' . uniqid();
        
        // Create first template
        $this->executeCommandSuccessfully([
            'template:create',
            $templateName
        ]);
        
        // Try to create duplicate
        $process = $this->executeCommand([
            'template:create',
            $templateName
        ]);
        
        $output = $process->getOutput();
        $this->assertNotEmpty($output);
        
        // Cleanup
        $this->queryDatabase('DELETE FROM modx_site_templates WHERE templatename = ?', [$templateName]);
    }

    /**
     * Clean up test data
     */
    protected function tearDown(): void
    {
        // Remove any leftover test templates
        $this->queryDatabase('DELETE FROM modx_site_templates WHERE templatename LIKE ?', ['IntegrationTestTemplate_%']);
        $this->queryDatabase('DELETE FROM modx_categories WHERE category LIKE ?', ['IntegrationTestCategory_%']);
        
        parent::tearDown();
    }
}
