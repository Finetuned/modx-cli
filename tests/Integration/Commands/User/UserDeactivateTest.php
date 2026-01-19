<?php

namespace MODX\CLI\Tests\Integration\Commands\User;

/**
 * Integration test for user:deactivate command
 */
class UserDeactivateTest extends UserTestBase
{
    public function testUserDeactivateClearsActive()
    {
        if (!$this->commandExists('user:deactivate')) {
            $this->markTestSkipped('user:deactivate command not available.');
        }

        $username = 'integration_user_' . uniqid();
        $email = 'integration+' . uniqid() . '@example.com';
        $userId = $this->createUser($username, $email, 1, 0);

        $this->executeCommandSuccessfully([
            'user:deactivate',
            (string) $userId,
        ]);

        $rows = $this->queryDatabase(
            'SELECT active FROM ' . $this->usersTable . ' WHERE id = ?',
            [$userId]
        );
        $this->assertNotEmpty($rows);
        $this->assertSame('0', (string) $rows[0]['active']);
    }
}
