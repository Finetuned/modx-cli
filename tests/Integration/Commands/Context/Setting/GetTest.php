<?php

namespace MODX\CLI\Tests\Integration\Commands\Context\Setting;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;

/**
 * Integration test for context:setting:get command
 */
class GetTest extends BaseIntegrationTest
{
    /**
     * Test that context:setting:get executes successfully
     */
    public function testContextSettingGetExecutesSuccessfully()
    {
        $contextKey = 'web'; // Use default web context
        $settingKey = 'site_name';
        
        $process = $this->executeCommandSuccessfully([
            'context:setting:get',
            $contextKey,
            $settingKey
        ]);
        
        $output = $process->getOutput();
        $this->assertNotEmpty($output);
    }

    /**
     * Test context:setting:get with JSON output
     */
    public function testContextSettingGetReturnsValidJson()
    {
        $contextKey = 'web';
        $settingKey = 'site_name';
        
        $data = $this->executeCommandJson([
            'context:setting:get',
            $contextKey,
            $settingKey
        ]);
        
        $this->assertIsArray($data);
        $this->assertArrayHasKey('success', $data);
    }

    /**
     * Test context:setting:get with custom setting
     */
    public function testContextSettingGetWithCustomSetting()
    {
        $contextKey = 'integtest_' . uniqid();
        $settingKey = 'test_setting';
        
        // Create test context
        $this->queryDatabase(
            'INSERT INTO ' . $this->getTableName('context') . ' (key, name, description, rank) VALUES (?, ?, ?, ?)',
            [$contextKey, 'Test Context', 'Test Description', 0]
        );
        
        // Create context setting
        $this->queryDatabase(
            'INSERT INTO ' . $this->getTableName('context_setting') . ' (context_key, `key`, value, xtype, namespace, area, editedon) VALUES (?, ?, ?, ?, ?, ?, NOW())',
            [$contextKey, $settingKey, 'test_value', 'textfield', 'core', '']
        );
        
        $data = $this->executeCommandJson([
            'context:setting:get',
            $contextKey,
            $settingKey
        ]);
        
        $this->assertTrue($data['success']);
        $this->assertEquals('test_value', $data['object']['value']);
        
        // Cleanup
        $this->queryDatabase('DELETE FROM ' . $this->getTableName('context_setting') . ' WHERE context_key = ?', [$contextKey]);
        $this->queryDatabase('DELETE FROM ' . $this->getTableName('context') . ' WHERE key = ?', [$contextKey]);
    }

    /**
     * Test context:setting:get with non-existent setting
     */
    public function testContextSettingGetWithNonExistentSetting()
    {
        $process = $this->executeCommand([
            'context:setting:get',
            'web',
            'nonexistent_setting_' . uniqid()
        ]);
        
        $output = $process->getOutput();
        $this->assertNotEmpty($output);
    }

    /**
     * Clean up test data
     */
    protected function tearDown(): void
    {
        // Remove any leftover test context settings
        $this->queryDatabase('DELETE FROM ' . $this->getTableName('context_setting') . ' WHERE context_key LIKE ?', ['integtest_%']);
        $this->queryDatabase('DELETE FROM ' . $this->getTableName('context') . ' WHERE key LIKE ?', ['integtest_%']);
        
        parent::tearDown();
    }
}