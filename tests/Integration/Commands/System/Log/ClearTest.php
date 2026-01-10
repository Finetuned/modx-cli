<?php

namespace MODX\CLI\Tests\Integration\Commands\System\Log;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;

/**
 * Integration test for system:log:clear command
 */
class ClearTest extends BaseIntegrationTest
{
    public function testLogClearReturnsValidJson()
    {
        $data = $this->executeCommandJson([
            'system:log:clear'
        ]);

        $this->assertArrayHasKey('success', $data);
        $this->assertTrue($data['success']);
    }
}
