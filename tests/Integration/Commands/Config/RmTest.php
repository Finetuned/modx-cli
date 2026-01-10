<?php

namespace MODX\CLI\Tests\Integration\Commands\Config;

/**
 * Integration test for config:rm command
 */
class RmTest extends ConfigTestCase
{
    public function testRemoveDeletesInstance()
    {
        $name = 'integ_config_' . uniqid();

        $this->runConfigCommand([
            'config:add',
            $name,
            '--base_path=' . $this->modxPath,
            '--json',
        ]);

        $process = $this->runConfigCommand([
            'config:rm',
            $name,
            '--json',
        ]);

        $this->assertSame(0, $process->getExitCode());
        $data = json_decode($process->getOutput(), true);
        $this->assertTrue($data['success']);
        $this->assertSame($name, $data['instance']['name']);

        $instances = $this->readJsonFile($this->instancesFile);
        $this->assertArrayNotHasKey($name, $instances);
    }
}
