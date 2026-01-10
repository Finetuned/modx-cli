<?php

namespace MODX\CLI\Tests\Integration\Commands\Resource;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;

/**
 * Integration test for resource:delete command
 */
class ResourceDeleteTest extends BaseIntegrationTest
{
    protected string $resourcesTable;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resourcesTable = $this->getTableName('site_content');
    }

    public function testResourceDeleteExecutesSuccessfully()
    {
        $pageTitle = 'IntegrationTestResource_' . uniqid();
        $resourceId = $this->createResource($pageTitle);

        $process = $this->executeCommandSuccessfully([
            'resource:delete',
            $resourceId,
            '--force'
        ]);

        $output = $process->getOutput();
        $this->assertStringContainsString('deleted successfully', $output);

        $rows = $this->queryDatabase(
            'SELECT deleted FROM ' . $this->resourcesTable . ' WHERE id = ?',
            [$resourceId]
        );
        $this->assertEquals(1, (int) $rows[0]['deleted']);
    }

    public function testResourceDeleteReturnsValidJson()
    {
        $pageTitle = 'IntegrationTestResource_' . uniqid();
        $resourceId = $this->createResource($pageTitle);

        $data = $this->executeCommandJson([
            'resource:delete',
            $resourceId,
            '--force'
        ]);

        $this->assertArrayHasKey('success', $data);
        $this->assertTrue($data['success']);
    }

    public function testResourceDeleteWithInvalidId()
    {
        $process = $this->executeCommand([
            'resource:delete',
            '999999',
            '--force'
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
        parent::tearDown();
    }
}
