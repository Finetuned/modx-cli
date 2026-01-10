<?php

namespace MODX\CLI\Tests\Integration\Commands\Misc;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;

/**
 * Integration test for download command
 */
class DownloadTest extends BaseIntegrationTest
{
    public function testDownloadSkipsWhenFileExists()
    {
        $version = '3.0.0';
        $dir = $this->modxPath . '/tmp/modx-cli-download-' . uniqid() . '/';
        $filename = $dir . 'modx-' . $version . '.zip';

        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($filename, 'test');

        $process = $this->executeCommand([
            'download',
            $version,
            $dir,
            '--json',
        ]);

        $data = json_decode($process->getOutput(), true);
        $this->assertIsArray($data);
        $this->assertTrue($data['success']);
        $this->assertTrue($data['already_downloaded']);

        @unlink($filename);
        @rmdir($dir);
    }
}
