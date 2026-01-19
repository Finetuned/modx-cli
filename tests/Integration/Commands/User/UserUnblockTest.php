<?php

namespace MODX\CLI\Tests\Integration\Commands\User;

/**
 * Integration test for user:unblock command
 */
class UserUnblockTest extends UserTestBase
{
    public function testUserUnblockClearsBlocked()
    {
        if (!$this->commandExists('user:unblock')) {
            $this->markTestSkipped('user:unblock command not available.');
        }

        $username = 'integration_user_' . uniqid();
        $email = 'integration+' . uniqid() . '@example.com';
        $userId = $this->createUser($username, $email, 1, 1);

        $this->executeCommandSuccessfully([
            'user:unblock',
            (string) $userId,
        ]);

        $rows = $this->queryDatabase(
            'SELECT blocked FROM ' . $this->userAttributesTable . ' WHERE internalKey = ?',
            [$userId]
        );
        $this->assertNotEmpty($rows);
        $this->assertSame('0', (string) $rows[0]['blocked']);
    }
}
