<?php

namespace MODX\CLI\Tests\Integration\Commands\Source;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;

/**
 * Integration test for source:list command
 */
class GetListTest extends BaseIntegrationTest
{
    /**
     * Test that source:list executes successfully
     */
    public function testSourceListExecutesSuccessfully()
    {
        $sourceName = 'integtest_' . uniqid();
        $this->executeCommandSuccessfully([
            'source:create',
            $sourceName
        ]);

        $process = $this->executeCommandSuccessfully([
            'source:list',
            '--limit=0'
        ]);

        $output = $process->getOutput();
        $this->assertStringContainsString($sourceName, $output);

        $this->queryDatabase('DELETE FROM ' . $this->getTableName('media_sources') . ' WHERE name = ?', [$sourceName]);
    }

    /**
     * Test source:list with JSON output
     */
    public function testSourceListReturnsValidJson()
    {
        $sourceName = 'integtest_' . uniqid();
        $this->executeCommandSuccessfully([
            'source:create',
            $sourceName
        ]);

        $data = $this->executeCommandJson([
            'source:list',
            '--limit=0'
        ]);

        $this->assertArrayHasKey('results', $data);
        $this->assertIsArray($data['results']);

        $found = false;
        foreach ($data['results'] as $row) {
            if (isset($row['name']) && $row['name'] === $sourceName) {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found, 'Created source not found in list results.');

        $this->queryDatabase('DELETE FROM ' . $this->getTableName('media_sources') . ' WHERE name = ?', [$sourceName]);
    }

    public function testSourceListHonorsPagination()
    {
        $sourceName = 'integtest_' . uniqid();
        $this->executeCommandSuccessfully([
            'source:create',
            $sourceName
        ]);

        $data = $this->executeCommandJson([
            'source:list',
            '--limit=1',
            '--start=0'
        ]);

        $this->assertArrayHasKey('results', $data);
        $this->assertIsArray($data['results']);
        $this->assertLessThanOrEqual(1, count($data['results']));

        $this->queryDatabase('DELETE FROM ' . $this->getTableName('media_sources') . ' WHERE name = ?', [$sourceName]);
    }

    protected function tearDown(): void
    {
        $this->queryDatabase('DELETE FROM ' . $this->getTableName('media_sources') . ' WHERE name LIKE ?', ['integtest_%']);
        parent::tearDown();
    }
}
