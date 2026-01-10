<?php

namespace MODX\CLI\Tests\Integration\Commands\Resource;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;

/**
 * Integration test for resource:list command
 */
class ResourceListTest extends BaseIntegrationTest
{
    protected string $resourcesTable;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resourcesTable = $this->getTableName('site_content');
    }

    public function testResourceListExecutesSuccessfully()
    {
        $pageTitle = 'IntegrationTestResource_' . uniqid();
        $this->createResource($pageTitle);

        $process = $this->executeCommandSuccessfully([
            'resource:list',
            '--limit=0'
        ]);

        $output = $process->getOutput();
        $this->assertStringContainsString($pageTitle, $output);
    }

    public function testResourceListReturnsValidJson()
    {
        $pageTitle = 'IntegrationTestResource_' . uniqid();
        $this->createResource($pageTitle);

        $data = $this->executeCommandJson([
            'resource:list',
            '--limit=0'
        ]);

        $this->assertArrayHasKey('results', $data);
        $this->assertIsArray($data['results']);

        $found = false;
        foreach ($data['results'] as $row) {
            if (isset($row['pagetitle']) && $row['pagetitle'] === $pageTitle) {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found, 'Created resource not found in list results.');
    }

    public function testResourceListWithContextFilter()
    {
        $pageTitle = 'IntegrationTestResource_' . uniqid();
        $this->createResource($pageTitle);

        $data = $this->executeCommandJson([
            'resource:list',
            '--context=web',
            '--limit=0'
        ]);

        $this->assertArrayHasKey('results', $data);
    }

    public function testResourceListWithParentAndPublishedFilters()
    {
        $parentTitle = 'IntegrationTestResourceParent_' . uniqid();
        $childTitle = 'IntegrationTestResource_' . uniqid();

        $parentId = $this->createResource($parentTitle);

        $this->executeCommandSuccessfully([
            'resource:create',
            $childTitle,
            '--parent=' . $parentId,
            '--published=0',
            '--hidemenu=1',
            '--content=Child content'
        ]);

        $data = $this->executeCommandJson([
            'resource:list',
            '--parent=' . $parentId,
            '--published=0',
            '--hidemenu=1',
            '--limit=0'
        ]);

        $this->assertArrayHasKey('results', $data);
        $this->assertIsArray($data['results']);

        $found = false;
        foreach ($data['results'] as $row) {
            if (isset($row['pagetitle']) && $row['pagetitle'] === $childTitle) {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found, 'Filtered resource not found in list results.');

        $this->queryDatabase(
            'DELETE FROM ' . $this->resourcesTable . ' WHERE pagetitle IN (?, ?)',
            [$parentTitle, $childTitle]
        );
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
        $this->queryDatabase('DELETE FROM ' . $this->resourcesTable . ' WHERE pagetitle LIKE ?', ['IntegrationTestResourceParent_%']);
        parent::tearDown();
    }
}
