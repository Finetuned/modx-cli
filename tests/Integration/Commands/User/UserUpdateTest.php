<?php

namespace MODX\CLI\Tests\Integration\Commands\User;

/**
 * Integration test for user:update command
 */
class UserUpdateTest extends UserTestBase
{
    public function testUserUpdatePersistsChanges()
    {
        $username = 'integration_user_' . uniqid();
        $email = 'integration+' . uniqid() . '@example.com';
        $userId = $this->createUser($username, $email, 1, 0);

        $updatedEmail = 'updated+' . uniqid() . '@example.com';

        $process = $this->executeCommandSuccessfully([
            'user:update',
            (string) $userId,
            '--fullname=Updated Integration User',
            '--email=' . $updatedEmail,
            '--active=0',
            '--blocked=1',
        ]);

        $output = $process->getOutput();
        $this->assertStringContainsString('User updated successfully', $output);

        $userRows = $this->queryDatabase(
            'SELECT active FROM ' . $this->usersTable . ' WHERE id = ?',
            [$userId]
        );
        $this->assertNotEmpty($userRows);
        $this->assertSame('0', (string) $userRows[0]['active']);

        $profileRows = $this->queryDatabase(
            'SELECT email, fullname, blocked FROM ' . $this->userAttributesTable . ' WHERE internalKey = ?',
            [$userId]
        );
        $this->assertNotEmpty($profileRows);
        $this->assertSame($updatedEmail, $profileRows[0]['email']);
        $this->assertSame('Updated Integration User', $profileRows[0]['fullname']);
        $this->assertSame('1', (string) $profileRows[0]['blocked']);
    }
}
