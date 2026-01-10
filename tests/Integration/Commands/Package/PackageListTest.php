<?php

namespace MODX\CLI\Tests\Integration\Commands\Package;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;

/**
 * Integration test for package:list command
 */
class PackageListTest extends BaseIntegrationTest
{
    public function testPackageListExecutesSuccessfully()
    {
        $process = $this->executeCommandSuccessfully([
            'package:list',
            '--limit=0'
        ]);

        $output = $process->getOutput();
        $this->assertNotEmpty($output);
    }

    public function testPackageListReturnsValidJson()
    {
        $data = $this->executeCommandJson([
            'package:list',
            '--limit=0'
        ]);

        $this->assertArrayHasKey('results', $data);
        $this->assertIsArray($data['results']);
        $this->assertArrayHasKey('total', $data);
    }

    public function testPackageListHonorsPagination()
    {
        $data = $this->executeCommandJson([
            'package:list',
            '--limit=1',
            '--start=0'
        ]);

        $this->assertArrayHasKey('results', $data);
        $this->assertIsArray($data['results']);
        $this->assertLessThanOrEqual(1, count($data['results']));
    }
}
