<?php

namespace MODX\CLI\Tests\Integration\Commands\Config;

/**
 * Integration test for config:rename command
 */
class RenameTest extends ConfigTestCase
{
    public function testRenameUpdatesInstanceAndDefault()
    {
        $oldName = 'integ_config_' . uniqid();
        $newName = 'integ_config_' . uniqid();

        $this->runConfigCommand([
            'config:add',
            $oldName,
            '--base_path=' . $this->modxPath,
            '--default',
            '--json',
        ]);

        $process = $this->runConfigCommand([
            'config:rename',
            $oldName,
            $newName,
            '--json',
        ]);

        $this->assertSame(0, $process->getExitCode());
        $data = json_decode($process->getOutput(), true);
        $this->assertTrue($data['success']);
        $this->assertSame($oldName, $data['instance']['old_name']);
        $this->assertSame($newName, $data['instance']['new_name']);
        $this->assertTrue($data['instance']['is_default']);

        $instances = $this->readJsonFile($this->instancesFile);
        $this->assertArrayNotHasKey($oldName, $instances);
        $this->assertArrayHasKey($newName, $instances);
        $this->assertSame($newName, $instances['__default__']['class']);
    }
}
