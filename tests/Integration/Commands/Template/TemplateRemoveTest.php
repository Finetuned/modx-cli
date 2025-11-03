<?php

namespace MODX\CLI\Tests\Integration\Commands\Template;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;

/**
 * Integration test for template:remove command
 */
class TemplateRemoveTest extends BaseIntegrationTest
{
    /**
     * Test that template:remove deletes a template
     */
    public function testTemplateRemoveExecutesSuccessfully()
    {
        $templateName = 'IntegrationTestTemplate_' . uniqid();
        
        // Create template first
        $this->executeCommandSuccessfully([
            'template:create',
            $templateName,
            '--content=<html><body>Test</body></html>'
        ]);
        
        // Get the template ID
        $rows = $this->queryDatabase('SELECT id FROM ' . $this->templatesTable . ' WHERE templatename = ?', [$templateName]);
        $templateId = $rows[0]['id'];
        
        // Verify template exists
        $beforeCount = $this->countTableRows($this->templatesTable, 'id = ?', [$templateId]);
        $this->assertEquals(1, $beforeCount);
        
        // Remove template
        $process = $this->executeCommandSuccessfully([
            'template:remove',
            $templateId
        ]);
        
        $output = $process->getOutput();
        $this->assertStringContainsString('removed successfully', $output);
        
        // Verify template no longer exists
        $afterCount = $this->countTableRows($this->templatesTable, 'id = ?', [$templateId]);
        $this->assertEquals(0, $afterCount);
    }

    /**
     * Test template:remove with JSON output
     */
    public function testTemplateRemoveReturnsValidJson()
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
        
        // Remove template with JSON
        $data = $this->executeCommandJson([
            'template:remove',
            $templateId
        ]);
        
        $this->assertIsArray($data);
        $this->assertArrayHasKey('success', $data);
        $this->assertTrue($data['success']);
    }

    /**
     * Test error handling for non-existent template
     */
    public function testTemplateRemoveWithInvalidId()
    {
        $process = $this->executeCommand([
            'template:remove',
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
        $this->queryDatabase('DELETE FROM ' . $this->templatesTable . ' WHERE templatename LIKE ?', ['IntegrationTestTemplate_%']);
        parent::tearDown();
    }
}
