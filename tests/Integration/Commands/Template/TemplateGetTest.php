<?php

namespace MODX\CLI\Tests\Integration\Commands\Template;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;

/**
 * Integration test for template:get command
 */
class TemplateGetTest extends BaseIntegrationTest
{
    /**
     * Test that template:get retrieves existing template
     */
    public function testTemplateGetExecutesSuccessfully()
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

        // Get template
        $process = $this->executeCommandSuccessfully([
            'template:get',
            $templateId
        ]);

        $output = $process->getOutput();
        $this->assertStringContainsString($templateName, $output);

        // Cleanup
        $this->queryDatabase('DELETE FROM ' . $this->templatesTable . ' WHERE id = ?', [$templateId]);
    }

    /**
     * Test template:get with JSON output
     */
    public function testTemplateGetReturnsValidJson()
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

        // Get template with JSON
        $data = $this->executeCommandJson([
            'template:get',
            $templateId
        ]);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('id', $data);
        $this->assertEquals($templateId, $data['id']);
        $this->assertEquals($templateName, $data['templatename']);

        // Cleanup
        $this->queryDatabase('DELETE FROM ' . $this->templatesTable . ' WHERE id = ?', [$templateId]);
    }

    /**
     * Test error handling for non-existent template
     */
    public function testTemplateGetWithInvalidId()
    {
        $process = $this->executeCommand([
            'template:get',
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
