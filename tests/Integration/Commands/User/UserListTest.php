<?php

namespace MODX\CLI\Tests\Integration\Commands\User;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;

/**
 * Integration test for user:list command
 */
class UserListTest extends BaseIntegrationTest
{
    protected string $usersTable;
    protected string $userAttributesTable;

    protected function setUp(): void
    {
        parent::setUp();
        $this->usersTable = $this->getTableName('users');
        $this->userAttributesTable = $this->getTableName('user_attributes');
    }

    public function testUserListExecutesSuccessfully()
    {
        $process = $this->executeCommandSuccessfully([
            'user:list',
            '--limit=0'
        ]);

        $output = $process->getOutput();
        $this->assertNotEmpty($output);
    }

    public function testUserListReturnsValidJsonWithQueryFilter()
    {
        $username = 'integration_user_' . uniqid();
        $userId = $this->createUser($username, 'integration+' . uniqid() . '@example.com');

        $data = $this->executeCommandJson([
            'user:list',
            '--query=' . $username,
            '--limit=0'
        ]);

        $this->assertArrayHasKey('results', $data);
        $this->assertIsArray($data['results']);

        $found = false;
        foreach ($data['results'] as $row) {
            if (isset($row['username']) && $row['username'] === $username) {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found, 'Created user not found in list results.');

        $this->deleteUser($userId);
    }

    public function testUserListFiltersByActiveAndBlocked()
    {
        $username = 'integration_user_' . uniqid();
        $userId = $this->createUser($username, 'integration+' . uniqid() . '@example.com', 0, 1);

        $data = $this->executeCommandJson([
            'user:list',
            '--active=0',
            '--blocked=1',
            '--limit=0'
        ]);

        $this->assertArrayHasKey('results', $data);
        $this->assertIsArray($data['results']);

        $found = false;
        foreach ($data['results'] as $row) {
            if (isset($row['username']) && $row['username'] === $username) {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found, 'Filtered user not found in list results.');

        $this->deleteUser($userId);
    }

    protected function createUser(string $username, string $email, int $active = 1, int $blocked = 0): int
    {
        $userRow = $this->buildInsertRow($this->usersTable, [
            'username' => $username,
            'password' => 'integration-test',
            'class_key' => 'modUser',
            'active' => $active,
            'blocked' => $blocked,
            'createdon' => time()
        ]);

        $userId = $this->insertRow($this->usersTable, $userRow);

        $profileRow = $this->buildInsertRow($this->userAttributesTable, [
            'internalKey' => $userId,
            'fullname' => 'Integration Test User',
            'email' => $email
        ]);

        $this->insertRow($this->userAttributesTable, $profileRow);

        return $userId;
    }

    protected function deleteUser(int $userId): void
    {
        $this->queryDatabase('DELETE FROM ' . $this->userAttributesTable . ' WHERE internalKey = ?', [$userId]);
        $this->queryDatabase('DELETE FROM ' . $this->usersTable . ' WHERE id = ?', [$userId]);
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
