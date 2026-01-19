<?php

namespace MODX\CLI\Tests\Integration\Commands\User;

/**
 * Integration test for user:activate command
 */
class UserActivateTest extends UserTestBase
{
    public function testUserActivateSetsActive()
    {
        if (!$this->commandExists('user:activate')) {
            $this->markTestSkipped('user:activate command not available.');
        }

        $username = 'integration_user_' . uniqid();
        $email = 'integration+' . uniqid() . '@example.com';
        $userId = $this->createUser($username, $email, 0, 0);

        $this->executeCommandSuccessfully([
            'user:activate',
            (string) $userId,
        ]);

        $rows = $this->queryDatabase(
            'SELECT active FROM ' . $this->usersTable . ' WHERE id = ?',
            [$userId]
        );
        $this->assertNotEmpty($rows);
        $this->assertSame('1', (string) $rows[0]['active']);
    }
}
