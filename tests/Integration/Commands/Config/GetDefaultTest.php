<?php

namespace MODX\CLI\Tests\Integration\Commands\Config;

/**
 * Integration test for config:get-default command
 */
class GetDefaultTest extends ConfigTestCase
{
    public function testGetDefaultReturnsConfiguredInstance()
    {
        $name = 'integ_config_' . uniqid();

        $this->runConfigCommand([
            'config:add',
            $name,
            '--base_path=' . $this->modxPath,
            '--default',
            '--json',
        ]);

        $process = $this->runConfigCommand([
            'config:get-default',
            '--json',
        ]);

        $this->assertSame(0, $process->getExitCode());
        $data = json_decode($process->getOutput(), true);
        $this->assertTrue($data['success']);
        $this->assertSame($name, $data['default']['name']);
        $this->assertStringEndsWith('/', $data['default']['base_path']);
    }
}
