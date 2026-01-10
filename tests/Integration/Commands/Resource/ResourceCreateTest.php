<?php

namespace MODX\CLI\Tests\Integration\Commands\Resource;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;

/**
 * Integration test for resource:create command
 */
class ResourceCreateTest extends BaseIntegrationTest
{
    protected string $resourcesTable;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resourcesTable = $this->getTableName('site_content');
    }

    public function testResourceCreateExecutesSuccessfully()
    {
        $pageTitle = 'IntegrationTestResource_' . uniqid();

        $process = $this->executeCommandSuccessfully([
            'resource:create',
            $pageTitle,
            '--alias=integration-test-' . uniqid(),
            '--content=Integration test content'
        ]);

        $output = $process->getOutput();
        $this->assertStringContainsString('Resource created successfully', $output);

        $this->queryDatabase('DELETE FROM ' . $this->resourcesTable . ' WHERE pagetitle = ?', [$pageTitle]);
    }

    public function testResourceCreateReturnsValidJson()
    {
        $pageTitle = 'IntegrationTestResource_' . uniqid();

        $data = $this->executeCommandJson([
            'resource:create',
            $pageTitle,
            '--content=Integration test content'
        ]);

        $this->assertArrayHasKey('success', $data);
        $this->assertTrue($data['success']);
        $this->assertArrayHasKey('object', $data);
        $this->assertArrayHasKey('id', $data['object']);

        $this->queryDatabase('DELETE FROM ' . $this->resourcesTable . ' WHERE id = ?', [$data['object']['id']]);
    }

    public function testResourceCreationPersistsToDatabase()
    {
        $pageTitle = 'IntegrationTestResource_' . uniqid();
        $alias = 'integration-test-' . uniqid();

        $beforeCount = $this->countTableRows($this->resourcesTable, 'pagetitle = ?', [$pageTitle]);
        $this->assertEquals(0, $beforeCount);

        $this->executeCommandSuccessfully([
            'resource:create',
            $pageTitle,
            '--alias=' . $alias,
            '--published=0',
            '--hidemenu=1',
            '--content=Integration test content'
        ]);

        $afterCount = $this->countTableRows($this->resourcesTable, 'pagetitle = ?', [$pageTitle]);
        $this->assertEquals(1, $afterCount);

        $rows = $this->queryDatabase(
            'SELECT alias, published, hidemenu FROM ' . $this->resourcesTable . ' WHERE pagetitle = ?',
            [$pageTitle]
        );
        $this->assertEquals($alias, $rows[0]['alias']);
        $this->assertEquals(0, (int) $rows[0]['published']);
        $this->assertEquals(1, (int) $rows[0]['hidemenu']);

        $this->queryDatabase('DELETE FROM ' . $this->resourcesTable . ' WHERE pagetitle = ?', [$pageTitle]);
    }

    public function testResourceCreationWithParentTemplateAndContext()
    {
        $parentTitle = 'IntegrationTestResourceParent_' . uniqid();
        $childTitle = 'IntegrationTestResource_' . uniqid();
        $templateName = 'IntegrationTestTemplate_' . uniqid();

        $parentId = $this->createResourceWithContent($parentTitle, 'Parent content');

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

        $this->executeCommandSuccessfully([
            'resource:create',
            $childTitle,
            '--parent=' . $parentId,
            '--template=' . $templateId,
            '--context_key=web',
            '--content=Child content'
        ]);

        $rows = $this->queryDatabase(
            'SELECT parent, template, context_key FROM ' . $this->resourcesTable . ' WHERE pagetitle = ?',
            [$childTitle]
        );
        $this->assertEquals($parentId, (int) $rows[0]['parent']);
        $this->assertEquals($templateId, (int) $rows[0]['template']);
        $this->assertEquals('web', $rows[0]['context_key']);

        $this->queryDatabase('DELETE FROM ' . $this->resourcesTable . ' WHERE pagetitle IN (?, ?)', [$parentTitle, $childTitle]);
        $this->queryDatabase('DELETE FROM ' . $this->templatesTable . ' WHERE id = ?', [$templateId]);
    }

    protected function createResourceWithContent(string $pageTitle, string $content): int
    {
        $this->executeCommandSuccessfully([
            'resource:create',
            $pageTitle,
            '--content=' . $content
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
        $this->queryDatabase('DELETE FROM ' . $this->resourcesTable . ' WHERE pagetitle LIKE ?', ['IntegrationTestResourceParent_%']);
        $this->queryDatabase('DELETE FROM ' . $this->templatesTable . ' WHERE templatename LIKE ?', ['IntegrationTestTemplate_%']);
        parent::tearDown();
    }
}
