<?php

namespace MODX\CLI\Tests\Integration\Commands\Resource;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;

/**
 * Integration test for resource:get command
 */
class ResourceGetTest extends BaseIntegrationTest
{
    protected string $resourcesTable;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resourcesTable = $this->getTableName('site_content');
    }

    public function testResourceGetExecutesSuccessfully()
    {
        $pageTitle = 'IntegrationTestResource_' . uniqid();
        $resourceId = $this->createResource($pageTitle);

        $process = $this->executeCommandSuccessfully([
            'resource:get',
            $resourceId
        ]);

        $output = $process->getOutput();
        $this->assertStringContainsString($pageTitle, $output);
    }

    public function testResourceGetReturnsValidJson()
    {
        $pageTitle = 'IntegrationTestResource_' . uniqid();
        $resourceId = $this->createResource($pageTitle);

        $data = $this->executeCommandJson([
            'resource:get',
            $resourceId
        ]);

        $this->assertArrayHasKey('pagetitle', $data);
        $this->assertEquals($pageTitle, $data['pagetitle']);
    }

    public function testResourceGetWithInvalidId()
    {
        $process = $this->executeCommand([
            'resource:get',
            '999999',
            '--json'
        ]);

        $this->assertNotEquals(0, $process->getExitCode());

        $data = json_decode($process->getOutput(), true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('success', $data);
        $this->assertFalse($data['success']);
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
