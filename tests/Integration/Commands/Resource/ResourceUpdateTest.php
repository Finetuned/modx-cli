<?php

namespace MODX\CLI\Tests\Integration\Commands\Resource;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;

/**
 * Integration test for resource:update command
 */
class ResourceUpdateTest extends BaseIntegrationTest
{
    protected string $resourcesTable;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resourcesTable = $this->getTableName('site_content');
    }

    public function testResourceUpdateExecutesSuccessfully()
    {
        $pageTitle = 'IntegrationTestResource_' . uniqid();
        $newTitle = 'IntegrationTestResourceUpdated_' . uniqid();
        $resourceId = $this->createResource($pageTitle);

        $process = $this->executeCommandSuccessfully([
            'resource:update',
            $resourceId,
            '--pagetitle=' . $newTitle,
            '--content=Updated integration test content',
            '--published=0',
            '--hidemenu=1'
        ]);

        $output = $process->getOutput();
        $this->assertStringContainsString('Resource updated successfully', $output);

        $rows = $this->queryDatabase(
            'SELECT pagetitle, published, hidemenu FROM ' . $this->resourcesTable . ' WHERE id = ?',
            [$resourceId]
        );
        $this->assertEquals($newTitle, $rows[0]['pagetitle']);
        $this->assertEquals(0, (int) $rows[0]['published']);
        $this->assertEquals(1, (int) $rows[0]['hidemenu']);
    }

    public function testResourceUpdateReturnsValidJson()
    {
        $pageTitle = 'IntegrationTestResource_' . uniqid();
        $resourceId = $this->createResource($pageTitle);

        $data = $this->executeCommandJson([
            'resource:update',
            $resourceId,
            '--alias=integration-test-' . uniqid()
        ]);

        $this->assertArrayHasKey('success', $data);
        $this->assertTrue($data['success']);
    }

    public function testResourceUpdateWithInvalidId()
    {
        $process = $this->executeCommand([
            'resource:update',
            '999999',
            '--pagetitle=Invalid'
        ]);

        $output = $process->getOutput();
        $this->assertStringContainsString('not found', $output);
    }

    public function testResourceUpdateWithAdditionalOptions()
    {
        $pageTitle = 'IntegrationTestResource_' . uniqid();
        $resourceId = $this->createResource($pageTitle);

        $parentTitle = 'IntegrationTestResourceParent_' . uniqid();
        $parentId = $this->createResource($parentTitle);

        $templateName = 'IntegrationTestTemplate_' . uniqid();
        $this->executeCommandSuccessfully([
            'template:create',
            $templateName,
            '--content=<html><body>[[*content]]</body></html>'
        ]);

        $templateRows = $this->queryDatabase(
            'SELECT id FROM ' . $this->templatesTable . ' WHERE templatename = ?',
            [$templateName]
        );
        $templateId = $templateRows[0]['id'];

        $alias = 'integration-test-' . uniqid();

        $this->executeCommandSuccessfully([
            'resource:update',
            $resourceId,
            '--alias=' . $alias,
            '--parent=' . $parentId,
            '--template=' . $templateId,
            '--context_key=web'
        ]);

        $rows = $this->queryDatabase(
            'SELECT alias, parent, template, context_key FROM ' . $this->resourcesTable . ' WHERE id = ?',
            [$resourceId]
        );
        $this->assertEquals($alias, $rows[0]['alias']);
        $this->assertEquals($parentId, (int) $rows[0]['parent']);
        $this->assertEquals($templateId, (int) $rows[0]['template']);
        $this->assertEquals('web', $rows[0]['context_key']);

        $this->queryDatabase('DELETE FROM ' . $this->resourcesTable . ' WHERE id IN (?, ?)', [$resourceId, $parentId]);
        $this->queryDatabase('DELETE FROM ' . $this->templatesTable . ' WHERE id = ?', [$templateId]);
    }

    protected function createResource(string $pageTitle): int
    {
        $this->executeCommandSuccessfully([
            'resource:create',
            $pageTitle,
            '--content=Integration test content'
        ]);

        $rows = $this->queryDatabase(
            'SELECT id FROM ' . $this->resourcesTable . ' WHERE pagetitle = ?',
            [$pageTitle]
        );

        return (int) $rows[0]['id'];
    }

    protected function tearDown(): void
    {
        $this->queryDatabase('DELETE FROM ' . $this->resourcesTable . ' WHERE pagetitle LIKE ?', ['IntegrationTestResource_%']);
        $this->queryDatabase('DELETE FROM ' . $this->resourcesTable . ' WHERE pagetitle LIKE ?', ['IntegrationTestResourceUpdated_%']);
        parent::tearDown();
    }
}
