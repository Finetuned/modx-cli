<?php

namespace MODX\CLI\Tests\Integration\Commands\User;

/**
 * Integration test for user:resetpassword command
 */
class UserResetPasswordTest extends UserTestBase
{
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

}
