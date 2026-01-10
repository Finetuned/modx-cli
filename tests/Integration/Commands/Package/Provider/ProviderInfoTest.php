<?php

namespace MODX\CLI\Tests\Integration\Commands\Package\Provider;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;

/**
 * Integration test for package:provider:info command
 */
class ProviderInfoTest extends BaseIntegrationTest
{
    protected string $providersTable;

    protected function setUp(): void
    {
        parent::setUp();
        $this->providersTable = $this->getTableName('transport_providers');
    }

    public function testProviderInfoExecutesSuccessfully()
    {
        $providerName = 'IntegrationTestProvider_' . uniqid();
        $providerId = $this->createProvider($providerName);

        $process = $this->executeCommandSuccessfully([
            'package:provider:info',
            $providerId
        ]);

        $output = $process->getOutput();
        $this->assertStringContainsString($providerName, $output);

        $this->deleteProvider($providerId);
    }

    public function testProviderInfoReturnsValidJson()
    {
        $providerName = 'IntegrationTestProvider_' . uniqid();
        $providerId = $this->createProvider($providerName);

        $process = $this->executeCommandSuccessfully([
            'package:provider:info',
            $providerId,
            '--format=json'
        ]);

        $data = json_decode($process->getOutput(), true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('name', $data);
        $this->assertEquals($providerName, $data['name']);

        $this->deleteProvider($providerId);
    }

    public function testProviderInfoWithInvalidId()
    {
        $process = $this->executeCommand([
            'package:provider:info',
            '999999'
        ]);

        $this->assertNotEquals(0, $process->getExitCode());
        $this->assertStringContainsString('Provider not found', $process->getOutput());
    }

    protected function createProvider(string $name): int
    {
        $providerRow = $this->buildInsertRow($this->providersTable, [
            'name' => $name,
            'service_url' => 'http://example.invalid',
            'description' => 'Integration test provider',
            'verified' => 0
        ]);

        return $this->insertRow($this->providersTable, $providerRow);
    }

    protected function deleteProvider(int $providerId): void
    {
        $this->queryDatabase('DELETE FROM ' . $this->providersTable . ' WHERE id = ?', [$providerId]);
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
        $this->queryDatabase(
            'DELETE FROM ' . $this->providersTable . ' WHERE name LIKE ?',
            ['IntegrationTestProvider_%']
        );
        parent::tearDown();
    }
}
