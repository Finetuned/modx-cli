<?php

namespace MODX\CLI\Tests\Integration\Commands\Extra;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;

/**
 * Integration test for extra:list command
 */
class ExtraListTest extends BaseIntegrationTest
{
    protected string $namespacesTable;
    protected string $menusTable;

    protected function setUp(): void
    {
        parent::setUp();
        $this->namespacesTable = $this->getTableName('namespaces');
        $this->menusTable = $this->getTableName('menus');
    }

    public function testExtraListIncludesCreatedNamespace()
    {
        $namespace = 'integrationextra' . uniqid();
        $corePath = $this->modxPath . '/components/' . $namespace . '/';
        $assetsPath = $this->modxPath . '/assets/components/' . $namespace . '/';

        $this->executeCommandJson([
            'extra:add-component',
            $namespace,
            '--force',
        ]);

        $data = $this->executeCommandJson(['extra:list']);

        $this->assertArrayHasKey('results', $data);
        $this->assertIsArray($data['results']);

        $names = array_column($data['results'], 'name');
        $this->assertContains($namespace, $names);

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
