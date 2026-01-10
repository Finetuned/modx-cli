<?php

namespace MODX\CLI\Tests\Integration\Commands\Ns;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;

/**
 * Integration test for ns:create command
 */
class CreateTest extends BaseIntegrationTest
{
    protected string $namespacesTable;

    protected function setUp(): void
    {
        parent::setUp();
        $this->namespacesTable = $this->getTableName('namespaces');
    }

    public function testNamespaceCreateExecutesSuccessfully()
    {
        $name = 'integration_' . uniqid();
        $path = 'core/components/' . $name . '/';
        $assetsPath = 'assets/components/' . $name . '/';

        $process = $this->executeCommandSuccessfully([
            'ns:create',
            $name,
            '--path=' . $path,
            '--assets_path=' . $assetsPath
        ]);

        $output = $process->getOutput();
        $this->assertStringContainsString('Namespace created successfully', $output);

        $rows = $this->queryDatabase(
            'SELECT name, path, assets_path FROM ' . $this->namespacesTable . ' WHERE name = ?',
            [$name]
        );
        $this->assertEquals($name, $rows[0]['name']);
        $this->assertEquals($path, $rows[0]['path']);
        $this->assertEquals($assetsPath, $rows[0]['assets_path']);

        $this->queryDatabase('DELETE FROM ' . $this->namespacesTable . ' WHERE name = ?', [$name]);
    }

    public function testNamespaceCreateReturnsValidJson()
    {
        $name = 'integration_' . uniqid();

        $data = $this->executeCommandJson([
            'ns:create',
            $name
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
