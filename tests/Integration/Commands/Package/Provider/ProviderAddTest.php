<?php

namespace MODX\CLI\Tests\Integration\Commands\Package\Provider;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;

/**
 * Integration test for package:provider:add command
 */
class ProviderAddTest extends BaseIntegrationTest
{
    protected string $providersTable;

    protected function setUp(): void
    {
        parent::setUp();
        $this->providersTable = $this->getTableName('transport_providers');
    }

    public function testProviderAddReturnsValidationErrorForInvalidServiceUrl()
    {
        $providerName = 'IntegrationTestProvider_' . uniqid();

        $process = $this->executeCommand([
            'package:provider:add',
            $providerName,
            'http://example.invalid',
            '--description=Integration test provider',
            '--json'
        ]);

        $this->assertNotEquals(0, $process->getExitCode());
        $data = json_decode($process->getOutput(), true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('success', $data);
        $this->assertFalse($data['success']);
        $this->assertArrayHasKey('errors', $data);
    }

    protected function deleteProvider(int $providerId): void
    {
        $this->queryDatabase('DELETE FROM ' . $this->providersTable . ' WHERE id = ?', [$providerId]);
    }

    protected function tearDown(): void
    {
        $this->queryDatabase(
            'DELETE FROM ' . $this->providersTable . ' WHERE name LIKE ?',
            ['IntegrationTestProvider_%']
        );
        parent::tearDown();
    }
}
