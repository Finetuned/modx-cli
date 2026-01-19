<?php

namespace MODX\CLI\Tests\Integration\Commands\User;

/**
 * Integration test for user:list command
 */
class UserListTest extends UserTestBase
{
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
}
