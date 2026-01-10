<?php

namespace MODX\CLI\Tests\Integration\Commands\Config;

/**
 * Integration test for config:set-default command
 */
class SetDefaultTest extends ConfigTestCase
{
    public function testSetDefaultUpdatesDefaultInstance()
    {
        $name = 'integ_config_' . uniqid();

        $this->runConfigCommand([
            'config:add',
            $name,
            '--base_path=' . $this->modxPath,
            '--json',
        ]);

        $process = $this->runConfigCommand([
            'config:set-default',
            $name,
            '--json',
        ]);

        $this->assertSame(0, $process->getExitCode());
        $data = json_decode($process->getOutput(), true);
        $this->assertTrue($data['success']);
        $this->assertSame($name, $data['default']['name']);

        $instances = $this->readJsonFile($this->instancesFile);
        $this->assertSame($name, $instances['__default__']['class']);
    }
}
