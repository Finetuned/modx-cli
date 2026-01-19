<?php

namespace MODX\CLI\Tests\Integration\Commands\User;

/**
 * Integration test for user:create command
 */
class UserCreateTest extends UserTestBase
{
    public function testUserCreateExecutesSuccessfully()
    {
        $username = 'integration_user_' . uniqid();
        $email = 'integration+' . uniqid() . '@example.com';

        $process = $this->executeCommandSuccessfully([
            'user:create',
            $username,
            '--email=' . $email,
            '--password=IntegrationPass123!',
            '--fullname=Integration Test User',
        ]);

        $output = $process->getOutput();
        $this->assertStringContainsString('User created successfully', $output);

        $userId = $this->fetchUserId($username);
        $this->assertGreaterThan(0, $userId);

        $profileRows = $this->queryDatabase(
            'SELECT email, fullname FROM ' . $this->userAttributesTable . ' WHERE internalKey = ?',
            [$userId]
        );
        $this->assertNotEmpty($profileRows);
        $this->assertSame($email, $profileRows[0]['email']);
        $this->assertSame('Integration Test User', $profileRows[0]['fullname']);
    }

    public function testUserCreateReturnsValidJson()
    {
        $username = 'integration_user_' . uniqid();
        $email = 'integration+' . uniqid() . '@example.com';

        $data = $this->executeCommandJson([
            'user:create',
            $username,
            '--email=' . $email,
            '--password=IntegrationPass123!',
        ]);

        $this->assertArrayHasKey('success', $data);
        $this->assertTrue($data['success']);
        $this->assertSame($username, $data['object']['username'] ?? null);
    }
}
