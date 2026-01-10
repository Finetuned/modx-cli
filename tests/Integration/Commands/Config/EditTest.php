<?php

namespace MODX\CLI\Tests\Integration\Commands\Config;

/**
 * Integration test for config:edit command
 */
class EditTest extends ConfigTestCase
{
    public function testEditUpdatesInstanceAndSetsDefault()
    {
        $name = 'integ_config_' . uniqid();

        $this->runConfigCommand([
            'config:add',
            $name,
            '--base_path=' . $this->modxPath,
            '--json',
        ]);

        $process = $this->runConfigCommand([
            'config:edit',
            $name,
            '--base_path=' . $this->modxPath,
            '--default',
            '--json',
        ]);

        $this->assertSame(0, $process->getExitCode());
        $data = json_decode($process->getOutput(), true);
        $this->assertTrue($data['success']);
        $this->assertTrue($data['instance']['is_default']);

        $instances = $this->readJsonFile($this->instancesFile);
        $this->assertSame($name, $instances['__default__']['class']);
        $this->assertSame($this->modxPath . '/', $instances[$name]['base_path']);
    }
}
