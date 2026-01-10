<?php

namespace MODX\CLI\Tests\Integration\Commands\System\Log;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;

/**
 * Integration test for system:log:view command
 */
class ViewTest extends BaseIntegrationTest
{
    protected string $managerLogTable;

    protected function setUp(): void
    {
        parent::setUp();
        $this->managerLogTable = $this->getTableName('manager_log');
    }

    public function testLogViewReturnsValidJson()
    {
        $message = 'Integration Log Entry ' . uniqid();
        $this->insertLogEntry($message, 'error');

        $data = $this->executeCommandJson([
            'system:log:view',
            '--limit=0'
        ]);

        $this->assertArrayHasKey('results', $data);
        $this->assertIsArray($data['results']);
        $this->assertArrayHasKey('total', $data);
        $this->assertIsInt($data['total']);
    }

    protected function insertLogEntry(string $message, string $action): void
    {
        $row = $this->buildInsertRow($this->managerLogTable, [
            'action' => $action,
            'name' => $message,
            'message' => $message,
            'user' => 0,
            'ip' => '127.0.0.1',
            'occurred' => date('Y-m-d H:i:s'),
        ]);

        $this->insertRow($this->managerLogTable, $row);
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

    protected function insertRow(string $table, array $row): void
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
    }

    protected function tearDown(): void
    {
        $this->queryDatabase('DELETE FROM ' . $this->managerLogTable);
        parent::tearDown();
    }
}
