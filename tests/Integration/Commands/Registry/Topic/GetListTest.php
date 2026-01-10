<?php

namespace MODX\CLI\Tests\Integration\Commands\Registry\Topic;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;

/**
 * Integration test for registry:topic:list command
 */
class GetListTest extends BaseIntegrationTest
{
    public function testRegistryTopicListReturnsValidJson()
    {
        $process = $this->executeCommand([
            'registry:topic:list',
            '--register=db',
            '--json'
        ]);

        $this->assertNotEquals(0, $process->getExitCode());
        $data = json_decode($process->getOutput(), true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('success', $data);
        $this->assertFalse($data['success']);
        $this->assertArrayHasKey('message', $data);
    }
}
