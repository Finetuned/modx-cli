<?php

namespace MODX\CLI\Tests\Integration\Commands\Menu;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;

/**
 * Integration test for menu:list command
 */
class MenuListTest extends BaseIntegrationTest
{
    public function testMenuListExecutesSuccessfully()
    {
        $process = $this->executeCommandSuccessfully([
            'menu:list',
            '--limit=0'
        ]);

        $output = $process->getOutput();
        $this->assertNotEmpty($output);
    }

    public function testMenuListReturnsValidJson()
    {
        $data = $this->executeCommandJson([
            'menu:list',
            '--limit=0'
        ]);

        $this->assertArrayHasKey('results', $data);
        $this->assertIsArray($data['results']);
        $this->assertArrayHasKey('total', $data);
    }
}
