<?php

namespace MODX\CLI\Tests\Integration\Commands\Extra;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;

/**
 * Integration test for extra:add-component command
 */
class AddComponentTest extends BaseIntegrationTest
{
    protected string $namespacesTable;
    protected string $menusTable;

    protected function setUp(): void
    {
        parent::setUp();
        $this->namespacesTable = $this->getTableName('namespaces');
        $this->menusTable = $this->getTableName('menus');
    }

    public function testAddComponentCreatesNamespaceAndDirectories()
    {
        $namespace = 'integrationextra' . uniqid();
        $corePath = $this->modxPath . '/components/' . $namespace . '/';
        $assetsPath = $this->modxPath . '/assets/components/' . $namespace . '/';

        $data = $this->executeCommandJson([
            'extra:add-component',
            $namespace,
            '--force'
        ]);

        $this->assertArrayHasKey('success', $data);
        $this->assertTrue($data['success']);
        $this->assertEquals($namespace, $data['namespace']);

        $rows = $this->queryDatabase(
            'SELECT name, path, assets_path FROM ' . $this->namespacesTable . ' WHERE name = ?',
            [$namespace]
        );
        $this->assertCount(1, $rows);

        $this->assertTrue(is_dir($corePath));
        $this->assertTrue(is_dir($assetsPath));

        $this->cleanupNamespace($namespace, $corePath, $assetsPath);
    }

    protected function cleanupNamespace(string $namespace, string $corePath, string $assetsPath): void
    {
        $this->queryDatabase('DELETE FROM ' . $this->namespacesTable . ' WHERE name = ?', [$namespace]);
        $this->queryDatabase(
            'DELETE FROM ' . $this->menusTable . ' WHERE namespace = ? AND action = ?',
            [$namespace, 'index']
        );
        $this->removeDirectory($corePath);
        $this->removeDirectory($assetsPath);
    }

    protected function removeDirectory(string $dir): void
    {
        if (!file_exists($dir)) {
            return;
        }

        if (!is_dir($dir)) {
            @unlink($dir);
            return;
        }

        foreach (scandir($dir) as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $this->removeDirectory($dir . DIRECTORY_SEPARATOR . $item);
        }

        @rmdir($dir);
    }
}
