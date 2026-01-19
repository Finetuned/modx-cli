<?php

namespace MODX\CLI\Tests\Integration\Commands\User;

/**
 * Integration test for user:remove command
 */
class UserRemoveTest extends UserTestBase
{
    public function testUserRemoveDeletesUser()
    {
        $username = 'integration_user_' . uniqid();
        $email = 'integration+' . uniqid() . '@example.com';
        $userId = $this->createUser($username, $email);

        $process = $this->executeCommandSuccessfully([
            'user:remove',
            (string) $userId,
            '--force',
        ]);

        $output = $process->getOutput();
        $this->assertStringContainsString('User removed successfully', $output);

        $this->assertSame(
            0,
            $this->countTableRows($this->usersTable, 'id = ?', [$userId])
        );
        $this->assertSame(
            0,
            $this->countTableRows($this->userAttributesTable, 'internalKey = ?', [$userId])
        );
    }
}
