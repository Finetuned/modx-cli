<?php

namespace MODX\CLI\Tests\Integration\Commands\Ns;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;

/**
 * Integration test for ns:update command
 */
class UpdateTest extends BaseIntegrationTest
{
    protected string $namespacesTable;

    protected function setUp(): void
    {
        parent::setUp();
        $this->namespacesTable = $this->getTableName('namespaces');
    }

    public function testNamespaceUpdateExecutesSuccessfully()
    {
        $name = 'integration_' . uniqid();
        $this->executeCommandSuccessfully([
            'ns:create',
            $name,
            '--path=core/components/' . $name . '/'
        ]);

        $newPath = 'core/components/' . $name . '/updated/';
        $newAssets = 'assets/components/' . $name . '/';

        $process = $this->executeCommandSuccessfully([
            'ns:update',
            $name,
            '--path=' . $newPath,
            '--assets_path=' . $newAssets
        ]);

        $output = $process->getOutput();
        $this->assertStringContainsString('Namespace updated successfully', $output);

        $rows = $this->queryDatabase(
            'SELECT path, assets_path FROM ' . $this->namespacesTable . ' WHERE name = ?',
            [$name]
        );
        $this->assertEquals($newPath, $rows[0]['path']);
        $this->assertEquals($newAssets, $rows[0]['assets_path']);

        $this->queryDatabase('DELETE FROM ' . $this->namespacesTable . ' WHERE name = ?', [$name]);
    }

    public function testNamespaceUpdateReturnsValidJson()
    {
        $name = 'integration_' . uniqid();
        $this->executeCommandSuccessfully([
            'ns:create',
            $name
        ]);

        $data = $this->executeCommandJson([
            'ns:update',
            $name,
            '--path=core/components/' . $name . '/'
        ]);

        $this->assertArrayHasKey('success', $data);
        $this->assertTrue($data['success']);

        $this->queryDatabase('DELETE FROM ' . $this->namespacesTable . ' WHERE name = ?', [$name]);
    }

    protected function tearDown(): void
    {
        $this->queryDatabase('DELETE FROM ' . $this->namespacesTable . ' WHERE name LIKE ?', ['integration_%']);
        parent::tearDown();
    }
}
