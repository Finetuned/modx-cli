<?php

namespace MODX\CLI\Tests\Integration\Commands\Ns;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;

/**
 * Integration test for ns:list command
 */
class GetListTest extends BaseIntegrationTest
{
    protected string $namespacesTable;

    protected function setUp(): void
    {
        parent::setUp();
        $this->namespacesTable = $this->getTableName('namespaces');
    }

    public function testNamespaceListReturnsValidJson()
    {
        $name = 'integration_' . uniqid();
        $this->executeCommandSuccessfully([
            'ns:create',
            $name
        ]);

        $data = $this->executeCommandJson([
            'ns:list',
            '--limit=0'
        ]);

        $this->assertArrayHasKey('results', $data);
        $this->assertIsArray($data['results']);

        $found = false;
        foreach ($data['results'] as $row) {
            if (isset($row['name']) && $row['name'] === $name) {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found, 'Created namespace not found in list results.');

        $this->queryDatabase('DELETE FROM ' . $this->namespacesTable . ' WHERE name = ?', [$name]);
    }

    protected function tearDown(): void
    {
        $this->queryDatabase('DELETE FROM ' . $this->namespacesTable . ' WHERE name LIKE ?', ['integration_%']);
        parent::tearDown();
    }
}
