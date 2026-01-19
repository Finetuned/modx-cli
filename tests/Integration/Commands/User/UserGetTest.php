<?php

namespace MODX\CLI\Tests\Integration\Commands\User;

/**
 * Integration test for user:get command
 */
class UserGetTest extends UserTestBase
{
    public function testUserGetReturnsValidJsonById()
    {
        $username = 'integration_user_' . uniqid();
        $email = 'integration+' . uniqid() . '@example.com';
        $userId = $this->createUser($username, $email);

        $data = $this->executeCommandJson([
            'user:get',
            (string) $userId,
        ]);

        $this->assertArrayHasKey('success', $data);
        $this->assertTrue($data['success']);
        $this->assertSame($username, $data['object']['username'] ?? null);
    }

    public function testUserGetExecutesSuccessfullyByUsername()
    {
        $username = 'integration_user_' . uniqid();
        $email = 'integration+' . uniqid() . '@example.com';
        $this->createUser($username, $email);

        $process = $this->executeCommandSuccessfully([
            'user:get',
            $username,
        ]);

        $output = $process->getOutput();
        $this->assertStringContainsString('Username:    ' . $username, $output);
    }
}
