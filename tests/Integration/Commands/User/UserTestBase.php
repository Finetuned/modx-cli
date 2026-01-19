<?php

namespace MODX\CLI\Tests\Integration\Commands\User;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;

abstract class UserTestBase extends BaseIntegrationTest
{
    protected string $usersTable;
    protected string $userAttributesTable;

    protected function setUp(): void
    {
        parent::setUp();
        $this->usersTable = $this->getTableName('users');
        $this->userAttributesTable = $this->getTableName('user_attributes');
    }

    protected function commandExists(string $command): bool
    {
        $process = $this->executeCommand([$command, '--help']);
        if ($process->getExitCode() === 0) {
            return true;
        }

        $output = $process->getErrorOutput() . $process->getOutput();
        return strpos($output, 'Command "' . $command . '" is not defined') === false;
    }

    protected function createUser(string $username, string $email, int $active = 1, int $blocked = 0): int
    {
        $userRow = $this->buildInsertRow($this->usersTable, [
            'username' => $username,
            'password' => 'integration-test',
            'class_key' => 'modUser',
            'active' => $active,
            'blocked' => $blocked,
            'createdon' => time(),
        ]);

        $userId = $this->insertRow($this->usersTable, $userRow);

        $profileRow = $this->buildInsertRow($this->userAttributesTable, [
            'internalKey' => $userId,
            'fullname' => 'Integration Test User',
            'email' => $email,
            'blocked' => $blocked,
        ]);

        $this->insertRow($this->userAttributesTable, $profileRow);

        return $userId;
    }

    protected function deleteUser(int $userId): void
    {
        $this->queryDatabase('DELETE FROM ' . $this->userAttributesTable . ' WHERE internalKey = ?', [$userId]);
        $this->queryDatabase('DELETE FROM ' . $this->usersTable . ' WHERE id = ?', [$userId]);
    }

    protected function fetchUserId(string $username): int
    {
        $rows = $this->queryDatabase(
            'SELECT id FROM ' . $this->usersTable . ' WHERE username = ? ORDER BY id DESC LIMIT 1',
            [$username]
        );
        if (empty($rows)) {
            return 0;
        }

        return (int) $rows[0]['id'];
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
        $pdo = $this->getTestDatabase();
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

        $stmt = $pdo->prepare($sql);
        $stmt->execute(array_values($row));

        return (int) $pdo->lastInsertId();
    }

    protected function tearDown(): void
    {
        $this->queryDatabase(
            'DELETE FROM ' . $this->usersTable . ' WHERE username LIKE ?',
            ['integration_user_%']
        );
        parent::tearDown();
    }
}
