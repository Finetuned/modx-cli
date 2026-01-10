<?php

namespace MODX\CLI\Tests\Integration\Commands\Resource;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;

/**
 * Integration test for resource:erase command
 */
class ResourceEraseTest extends BaseIntegrationTest
{
    protected string $resourcesTable;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resourcesTable = $this->getTableName('site_content');
    }

    public function testResourceEraseExecutesSuccessfully()
    {
        $pageTitle = 'IntegrationTestResource_' . uniqid();
        $resourceId = $this->createResource($pageTitle);

        $this->executeCommandSuccessfully([
            'resource:delete',
            $resourceId,
            '--force'
        ]);

        $process = $this->executeCommandSuccessfully([
            'resource:erase',
            $resourceId,
            '--force'
        ]);

        $output = $process->getOutput();
        $this->assertStringContainsString('erased successfully', $output);

        $afterCount = $this->countTableRows($this->resourcesTable, 'id = ?', [$resourceId]);
        $this->assertEquals(0, $afterCount);
    }

    public function testResourceEraseReturnsValidJson()
    {
        $pageTitle = 'IntegrationTestResource_' . uniqid();
        $resourceId = $this->createResource($pageTitle);

        $this->executeCommandSuccessfully([
            'resource:delete',
            $resourceId,
            '--force'
        ]);

        $data = $this->executeCommandJson([
            'resource:erase',
            $resourceId,
            '--force'
        ]);

        $this->assertArrayHasKey('success', $data);
        $this->assertTrue($data['success']);
    }

    public function testResourceEraseWithNonDeletedResource()
    {
        $pageTitle = 'IntegrationTestResource_' . uniqid();
        $resourceId = $this->createResource($pageTitle);

        $process = $this->executeCommand([
            'resource:erase',
            $resourceId,
            '--force'
        ]);

        $output = $process->getOutput();
        $this->assertStringContainsString('not in the trash', $output);
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
