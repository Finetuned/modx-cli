<?php

namespace MODX\CLI\Tests\Integration\Commands\Ns;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;

/**
 * Integration test for ns:remove command
 */
class RemoveTest extends BaseIntegrationTest
{
    protected string $namespacesTable;

    protected function setUp(): void
    {
        parent::setUp();
        $this->namespacesTable = $this->getTableName('namespaces');
    }

    public function testNamespaceRemoveExecutesSuccessfully()
    {
        $name = 'integration_' . uniqid();
        $this->executeCommandSuccessfully([
            'ns:create',
            $name
        ]);

        $process = $this->executeCommandSuccessfully([
            'ns:remove',
            $name,
            '--force'
        ]);

        $output = $process->getOutput();
        $this->assertStringContainsString('Namespace removed successfully', $output);

        $afterCount = $this->countTableRows($this->namespacesTable, 'name = ?', [$name]);
        $this->assertEquals(0, $afterCount);
    }

    public function testNamespaceRemoveWithInvalidName()
    {
        $process = $this->executeCommand([
            'ns:remove',
            'integration_missing_' . uniqid(),
            '--force'
        ]);

        $this->assertEquals(0, $process->getExitCode());
        $this->assertStringContainsString('not found', $process->getOutput());
    }

    protected function tearDown(): void
    {
        $this->queryDatabase('DELETE FROM ' . $this->namespacesTable . ' WHERE name LIKE ?', ['integration_%']);
        parent::tearDown();
    }
}
