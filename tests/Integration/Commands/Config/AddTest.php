<?php

namespace MODX\CLI\Tests\Integration\Commands\Config;

/**
 * Integration test for config:add command
 */
class AddTest extends ConfigTestCase
{
    public function testAddCreatesInstanceAndDefault()
    {
        $name = 'integ_config_' . uniqid();

        $process = $this->runConfigCommand([
            'config:add',
            $name,
            '--base_path=' . $this->modxPath,
            '--default',
            '--json',
        ]);

        $this->assertSame(0, $process->getExitCode());
        $data = json_decode($process->getOutput(), true);
        $this->assertTrue($data['success']);
        $this->assertSame($name, $data['instance']['name']);
        $this->assertTrue($data['instance']['is_default']);
        $this->assertStringEndsWith('/', $data['instance']['base_path']);

        $instances = $this->readJsonFile($this->instancesFile);
        $this->assertArrayHasKey($name, $instances);
        $this->assertSame($name, $instances['__default__']['class']);
    }
}
