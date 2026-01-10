<?php

namespace MODX\CLI\Tests\Integration\Commands\Extra;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;

/**
 * Integration test for extra:remove-component command
 */
class RemoveComponentTest extends BaseIntegrationTest
{
    protected string $namespacesTable;
    protected string $menusTable;

    protected function setUp(): void
    {
        parent::setUp();
        $this->namespacesTable = $this->getTableName('namespaces');
        $this->menusTable = $this->getTableName('menus');
    }

    public function testRemoveComponentDeletesNamespaceAndFiles()
    {
        $namespace = 'integrationextra' . uniqid();
        $corePath = $this->modxPath . '/components/' . $namespace . '/';
        $assetsPath = $this->modxPath . '/assets/components/' . $namespace . '/';

        $this->executeCommandJson([
            'extra:add-component',
            $namespace,
            '--force',
        ]);

        $removeData = $this->executeCommandJson([
            'extra:remove-component',
            $namespace,
            '--force',
            '--files',
        ]);

        $this->assertArrayHasKey('success', $removeData);
        $this->assertTrue($removeData['success']);
        $this->assertTrue($removeData['removed']);
        $this->assertEquals($namespace, $removeData['namespace']);

        $namespaceRows = $this->queryDatabase(
            'SELECT name FROM ' . $this->namespacesTable . ' WHERE name = ?',
            [$namespace]
        );
        $this->assertCount(0, $namespaceRows);

        $menuCount = $this->countTableRows(
            $this->menusTable,
            'namespace = ? AND action = ?',
            [$namespace, 'index']
        );
        $this->assertSame(0, $menuCount);

        $this->assertFalse(is_dir($corePath));
        $this->assertFalse(is_dir($assetsPath));
    }
}
