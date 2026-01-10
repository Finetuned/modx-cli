<?php

namespace MODX\CLI\Tests\Integration\Commands\Template;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;

/**
 * Integration test for template:update command
 */
class TemplateUpdateTest extends BaseIntegrationTest
{
    /**
     * Test that template:update modifies existing template
     */
    public function testTemplateUpdateExecutesSuccessfully()
    {
        $templateName = 'IntegrationTestTemplate_' . uniqid();
        $newContent = '<html><body>Updated template</body></html>';
        
        // Create template
        $this->executeCommandSuccessfully([
            'template:create',
            $templateName,
            '--content=<html><body>Original</body></html>'
        ]);
        
        // Get template ID
        $rows = $this->queryDatabase('SELECT id FROM ' . $this->templatesTable . ' WHERE templatename = ?', [$templateName]);
        $templateId = $rows[0]['id'];
        
        // Update template
        $process = $this->executeCommandSuccessfully([
            'template:update',
            $templateId,
            '--content=' . $newContent
        ]);
        
        $output = $process->getOutput();
        $this->assertStringContainsString('updated successfully', $output);
        
        // Verify update in database
        $updatedRows = $this->queryDatabase('SELECT content FROM ' . $this->templatesTable . ' WHERE id = ?', [$templateId]);
        $this->assertEquals($newContent, $updatedRows[0]['content']);
        
        // Cleanup
        $this->queryDatabase('DELETE FROM ' . $this->templatesTable . ' WHERE id = ?', [$templateId]);
    }

    /**
     * Test template:update with JSON output
     */
    public function testTemplateUpdateReturnsValidJson()
    {
        $templateName = 'IntegrationTestTemplate_' . uniqid();
        
        // Create template
        $this->executeCommandSuccessfully([
            'template:create',
            $templateName
        ]);
        
        // Get template ID
        $rows = $this->queryDatabase('SELECT id FROM ' . $this->templatesTable . ' WHERE templatename = ?', [$templateName]);
        $templateId = $rows[0]['id'];
        
        // Update with JSON
        $data = $this->executeCommandJson([
            'template:update',
            $templateId,
            '--content=<html><body>Updated</body></html>'
        ]);
        
        $this->assertIsArray($data);
        $this->assertArrayHasKey('success', $data);
        $this->assertTrue($data['success']);
        
        // Cleanup
        $this->queryDatabase('DELETE FROM ' . $this->templatesTable . ' WHERE id = ?', [$templateId]);
    }

    /**
     * Test template:update changes category
     */
    public function testTemplateUpdateCategory()
    {
        $templateName = 'IntegrationTestTemplate_' . uniqid();
        $categoryName = 'IntegrationTestCategory_' . uniqid();
        
        // Create template
        $this->executeCommandSuccessfully([
            'template:create',
            $templateName
        ]);
        
        // Create category
        $this->executeCommandSuccessfully([
            'category:create',
            $categoryName
        ]);
        
        // Get IDs
        $templateRows = $this->queryDatabase('SELECT id FROM ' . $this->templatesTable . ' WHERE templatename = ?', [$templateName]);
        $templateId = $templateRows[0]['id'];
        $catRows = $this->queryDatabase('SELECT id FROM ' . $this->categoriesTable . ' WHERE category = ?', [$categoryName]);
        $categoryId = $catRows[0]['id'];
        
        // Update template category
        $this->executeCommandSuccessfully([
            'template:update',
            $templateId,
            '--category=' . $categoryId
        ]);
        
        // Verify category updated
        $updatedRows = $this->queryDatabase('SELECT category FROM ' . $this->templatesTable . ' WHERE id = ?', [$templateId]);
        $this->assertEquals($categoryId, $updatedRows[0]['category']);
        
        // Cleanup
        $this->queryDatabase('DELETE FROM ' . $this->templatesTable . ' WHERE id = ?', [$templateId]);
        $this->queryDatabase('DELETE FROM ' . $this->categoriesTable . ' WHERE id = ?', [$categoryId]);
    }

    /**
     * Test template:update changes description
     */
    public function testTemplateUpdateDescription()
    {
        $templateName = 'IntegrationTestTemplate_' . uniqid();
        $newDescription = 'Updated template description';
        
        // Create template
        $this->executeCommandSuccessfully([
            'template:create',
            $templateName
        ]);
        
        // Get template ID
        $rows = $this->queryDatabase('SELECT id FROM ' . $this->templatesTable . ' WHERE templatename = ?', [$templateName]);
        $templateId = $rows[0]['id'];
        
        // Update description
        $this->executeCommandSuccessfully([
            'template:update',
            $templateId,
            '--description=' . $newDescription
        ]);
        
        // Verify description updated
        $updatedRows = $this->queryDatabase('SELECT description FROM ' . $this->templatesTable . ' WHERE id = ?', [$templateId]);
        $this->assertEquals($newDescription, $updatedRows[0]['description']);
        
        // Cleanup
        $this->queryDatabase('DELETE FROM ' . $this->templatesTable . ' WHERE id = ?', [$templateId]);
    }

    /**
     * Test template:update with additional options
     */
    public function testTemplateUpdateWithAdditionalOptions()
    {
        $templateName = 'IntegrationTestTemplate_' . uniqid();
        $updatedName = 'IntegrationTestTemplateUpdated_' . uniqid();
        $description = 'Updated integration description';
        $staticFile = 'core/components/test/templates/updated.tpl';
        $icon = 'icon-updated';

        $this->executeCommandSuccessfully([
            'template:create',
            $templateName
        ]);

        $rows = $this->queryDatabase('SELECT id FROM ' . $this->templatesTable . ' WHERE templatename = ?', [$templateName]);
        $templateId = $rows[0]['id'];

        $this->executeCommandSuccessfully([
            'template:update',
            $templateId,
            '--templatename=' . $updatedName,
            '--description=' . $description,
            '--locked=1',
            '--static=1',
            '--static_file=' . $staticFile,
            '--icon=' . $icon
        ]);

        $updatedRows = $this->queryDatabase(
            'SELECT templatename, description, locked, static, static_file, icon FROM ' . $this->templatesTable . ' WHERE id = ?',
            [$templateId]
        );

        $this->assertEquals($updatedName, $updatedRows[0]['templatename']);
        $this->assertEquals($description, $updatedRows[0]['description']);
        $this->assertEquals(1, (int) $updatedRows[0]['locked']);
        $this->assertEquals(1, (int) $updatedRows[0]['static']);
        $this->assertEquals($staticFile, $updatedRows[0]['static_file']);
        $this->assertEquals($icon, $updatedRows[0]['icon']);

        $this->queryDatabase('DELETE FROM ' . $this->templatesTable . ' WHERE id = ?', [$templateId]);
    }

    /**
     * Test error handling for non-existent template
     */
    public function testTemplateUpdateWithInvalidId()
    {
        $process = $this->executeCommand([
            'template:update',
            '999999',
            '--content=<html><body>Test</body></html>'
        ]);
        
        $output = $process->getOutput();
        $this->assertNotEmpty($output);
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
