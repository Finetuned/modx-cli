<?php

namespace MODX\CLI\Tests\Integration\Commands\User;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;

/**
 * Integration test for user:resetpassword command
 */
class UserResetPasswordTest extends BaseIntegrationTest
{
    protected string $usersTable;
    protected string $userAttributesTable;

    protected function setUp(): void
    {
        parent::setUp();
        $this->usersTable = $this->getTableName('users');
        $this->userAttributesTable = $this->getTableName('user_attributes');
    }

    public function testUserResetPasswordExecutesSuccessfully()
    {
        $username = 'integration_user_' . uniqid();
        $userId = $this->createUser($username, 'integration+' . uniqid() . '@example.com');

        $process = $this->executeCommandSuccessfully([
            'user:resetpassword',
            $userId,
            '--password=IntegrationPass123!'
        ]);

        $output = $process->getOutput();
        $this->assertStringContainsString('Password reset successfully', $output);

        $this->deleteUser($userId);
    }

    public function testUserResetPasswordReturnsValidJson()
    {
        $username = 'integration_user_' . uniqid();
        $userId = $this->createUser($username, 'integration+' . uniqid() . '@example.com');

        $data = $this->executeCommandJson([
            'user:resetpassword',
            $userId,
            '--password=IntegrationPass123!'
        ]);

        $this->assertArrayHasKey('success', $data);
        $this->assertTrue($data['success']);

        $this->deleteUser($userId);
    }

    public function testUserResetPasswordWithInvalidId()
    {
        $process = $this->executeCommand([
            'user:resetpassword',
            '999999',
            '--password=IntegrationPass123!'
        ]);

        $output = $process->getOutput();
        $this->assertStringContainsString('not found', $output);
    }

    protected function createUser(string $username, string $email): int
    {
        $userRow = $this->buildInsertRow($this->usersTable, [
            'username' => $username,
            'password' => 'integration-test',
            'class_key' => 'modUser',
            'active' => 1,
            'blocked' => 0,
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
            'DELETE FROM ' . $this->usersTable . ' WHERE username LIKE ?',
            ['integration_user_%']
        );
        parent::tearDown();
    }
}
