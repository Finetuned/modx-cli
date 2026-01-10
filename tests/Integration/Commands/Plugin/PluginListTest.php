<?php

namespace MODX\CLI\Tests\Integration\Commands\Plugin;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;

/**
 * Integration test for plugin:list command
 */
class PluginListTest extends BaseIntegrationTest
{
    protected string $pluginsTable;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pluginsTable = $this->getTableName('site_plugins');
    }

    public function testPluginListExecutesSuccessfully()
    {
        $pluginName = 'IntegrationTestPlugin_' . uniqid();
        $this->createPlugin($pluginName, 0);

        $process = $this->executeCommandSuccessfully([
            'plugin:list',
            '--limit=0'
        ]);

        $output = $process->getOutput();
        $this->assertStringContainsString($pluginName, $output);
    }

    public function testPluginListReturnsValidJson()
    {
        $pluginName = 'IntegrationTestPlugin_' . uniqid();
        $this->createPlugin($pluginName, 0);

        $data = $this->executeCommandJson([
            'plugin:list',
            '--limit=0'
        ]);

        $this->assertArrayHasKey('results', $data);
        $this->assertIsArray($data['results']);

        $found = false;
        foreach ($data['results'] as $row) {
            if (isset($row['name']) && $row['name'] === $pluginName) {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found, 'Created plugin not found in list results.');
    }

    protected function createPlugin(string $name, int $disabled): int
    {
        $pluginRow = $this->buildInsertRow($this->pluginsTable, [
            'name' => $name,
            'description' => 'Integration test plugin',
            'plugincode' => '',
            'disabled' => $disabled,
            'static' => 0,
            'category' => 0
        ]);

        return $this->insertRow($this->pluginsTable, $pluginRow);
    }

    protected function buildInsertRow(string $table, array $overrides): array
    {
        $columns = $this->queryDatabase('SHOW COLUMNS FROM ' . $table);
        $row = [];

        foreach ($columns as $column) {
            $field = $column['Field'];
            $isAutoIncrement = isset($column['Extra']) && $column['Extra'] === 'auto_increment';
            if ($isAutoIncrement) {
                continue;
            }

            if (array_key_exists($field, $overrides)) {
                $row[$field] = $overrides[$field];
                continue;
            }

            if ($column['Null'] === 'NO' && $column['Default'] === null) {
                $type = strtolower((string) $column['Type']);
                if (strpos($type, 'int') !== false || strpos($type, 'decimal') !== false) {
                    $row[$field] = 0;
                } elseif (strpos($type, 'datetime') !== false || strpos($type, 'timestamp') !== false) {
                    $row[$field] = date('Y-m-d H:i:s');
                } else {
                    $row[$field] = '';
                }
            } elseif ($column['Default'] !== null) {
                $row[$field] = $column['Default'];
            }
        }

        return $row;
    }

    protected function insertRow(string $table, array $row): int
    {
        $fields = array_keys($row);
        $placeholders = array_fill(0, count($fields), '?');
        $fieldsSql = implode(', ', array_map(function ($field) {
            return '`' . $field . '`';
        }, $fields));

        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $table,
            $fieldsSql,
            implode(', ', $placeholders)
        );

        $this->queryDatabase($sql, array_values($row));

        $rows = $this->queryDatabase('SELECT LAST_INSERT_ID() AS id');
        return (int) $rows[0]['id'];
    }

    protected function tearDown(): void
    {
        $this->queryDatabase('DELETE FROM ' . $this->pluginsTable . ' WHERE name LIKE ?', ['IntegrationTestPlugin_%']);
        parent::tearDown();
    }
}
