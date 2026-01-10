<?php

namespace MODX\CLI\Tests\Integration\Commands\Misc;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;

/**
 * Integration test for crawl command
 */
class CrawlTest extends BaseIntegrationTest
{
    public function testCrawlWithNoResourcesReturnsZero()
    {
        $data = $this->executeCommandJson([
            'crawl',
            'context_does_not_exist',
            '--json',
        ]);

        $this->assertTrue($data['success']);
        $this->assertSame(0, $data['total']);
    }
}
