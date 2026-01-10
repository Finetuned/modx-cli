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
