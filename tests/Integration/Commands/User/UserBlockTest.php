<?php

namespace MODX\CLI\Tests\Integration\Commands\User;

/**
 * Integration test for user:block command
 */
class UserBlockTest extends UserTestBase
{
    public function testUserBlockSetsBlocked()
    {
        if (!$this->commandExists('user:block')) {
            $this->markTestSkipped('user:block command not available.');
        }

        $username = 'integration_user_' . uniqid();
        $email = 'integration+' . uniqid() . '@example.com';
        $userId = $this->createUser($username, $email, 1, 0);

        $this->executeCommandSuccessfully([
            'user:block',
            (string) $userId,
        ]);

        $rows = $this->queryDatabase(
            'SELECT blocked FROM ' . $this->userAttributesTable . ' WHERE internalKey = ?',
            [$userId]
        );
        $this->assertNotEmpty($rows);
        $this->assertSame('1', (string) $rows[0]['blocked']);
    }
}
