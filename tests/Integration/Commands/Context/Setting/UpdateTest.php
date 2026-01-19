<?php

namespace MODX\CLI\Tests\Integration\Commands\Context\Setting;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;

/**
 * Integration test for context:setting:update command
 */
class UpdateTest extends BaseIntegrationTest
{
    /**
     * Test that context:setting:update executes successfully
     */
    public function testContextSettingUpdateExecutesSuccessfully()
    {
        $contextKey = 'integtest-' . uniqid();
        $settingKey = 'test_setting';

        // Create test context
        $this->queryDatabase(
            'INSERT INTO ' . $this->getTableName('context') . ' (`key`, name, description, rank) VALUES (?, ?, ?, ?)',
            [$contextKey, 'Test Context', 'Test Description', 0]
        );

        // Create context setting
        $this->queryDatabase(
            'INSERT INTO ' . $this->getTableName('context_setting') . ' (context_key, `key`, value, xtype, namespace, area, editedon) VALUES (?, ?, ?, ?, ?, ?, NOW())',
            [$contextKey, $settingKey, 'original_value', 'textfield', 'core', '']
        );

        $process = $this->executeCommandSuccessfully([
            'context:setting:update',
            $contextKey,
            $settingKey,
            '--value=updated_value',
            '--properties=namespace=core'
        ]);

        $output = $process->getOutput();
        $this->assertStringContainsString('updated successfully', $output);

        // Cleanup
        $this->queryDatabase('DELETE FROM ' . $this->getTableName('context_setting') . ' WHERE context_key = ?', [$contextKey]);
        $this->queryDatabase('DELETE FROM ' . $this->getTableName('context') . ' WHERE `key` = ?', [$contextKey]);
    }

    /**
     * Test context:setting:update with JSON output
     */
    public function testContextSettingUpdateReturnsValidJson()
    {
        $contextKey = 'integtest-' . uniqid();
        $settingKey = 'test_setting';

        // Create test context
        $this->queryDatabase(
            'INSERT INTO ' . $this->getTableName('context') . ' (`key`, name, description, rank) VALUES (?, ?, ?, ?)',
            [$contextKey, 'Test Context', 'Test Description', 0]
        );

        // Create context setting
        $this->queryDatabase(
            'INSERT INTO ' . $this->getTableName('context_setting') . ' (context_key, `key`, value, xtype, namespace, area, editedon) VALUES (?, ?, ?, ?, ?, ?, NOW())',
            [$contextKey, $settingKey, 'original_value', 'textfield', 'core', '']
        );

        $data = $this->executeCommandJson([
            'context:setting:update',
            $contextKey,
            $settingKey,
            '--value=updated_value',
            '--properties=namespace=core'
        ]);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('success', $data);
        $this->assertTrue($data['success']);

        // Cleanup
        $this->queryDatabase('DELETE FROM ' . $this->getTableName('context_setting') . ' WHERE context_key = ?', [$contextKey]);
        $this->queryDatabase('DELETE FROM ' . $this->getTableName('context') . ' WHERE `key` = ?', [$contextKey]);
    }

    /**
     * Test that update persists to database
     */
    public function testContextSettingUpdatePersistsToDatabase()
    {
        $contextKey = 'integtest-' . uniqid();
        $settingKey = 'test_setting';

        // Create test context
        $this->queryDatabase(
            'INSERT INTO ' . $this->getTableName('context') . ' (`key`, name, description, rank) VALUES (?, ?, ?, ?)',
            [$contextKey, 'Test Context', 'Test Description', 0]
        );

        // Create context setting
        $this->queryDatabase(
            'INSERT INTO ' . $this->getTableName('context_setting') . ' (context_key, `key`, value, xtype, namespace, area, editedon) VALUES (?, ?, ?, ?, ?, ?, NOW())',
            [$contextKey, $settingKey, 'original_value', 'textfield', 'core', '']
        );

        $this->executeCommandSuccessfully([
            'context:setting:update',
            $contextKey,
            $settingKey,
            '--value=new_value',
            '--properties=namespace=core'
        ]);

        // Verify update
        $rows = $this->queryDatabase(
            'SELECT value FROM ' . $this->getTableName('context_setting') . ' WHERE context_key = ? AND `key` = ?',
            [$contextKey, $settingKey]
        );
        $this->assertEquals('new_value', $rows[0]['value']);

        // Cleanup
        $this->queryDatabase('DELETE FROM ' . $this->getTableName('context_setting') . ' WHERE context_key = ?', [$contextKey]);
        $this->queryDatabase('DELETE FROM ' . $this->getTableName('context') . ' WHERE `key` = ?', [$contextKey]);
    }

    /**
     * Test update with non-existent setting
     */
    public function testContextSettingUpdateWithNonExistentSetting()
    {
        $process = $this->executeCommand([
            'context:setting:update',
            'web',
            'nonexistent_setting_' . uniqid(),
            '--value=test',
            '--properties=namespace=core'
        ]);

        $this->assertNotEquals(0, $process->getExitCode());
    }

    /**
     * Clean up test data
     */
    protected function tearDown(): void
    {
        // Remove any leftover test context settings
        $this->queryDatabase('DELETE FROM ' . $this->getTableName('context_setting') . ' WHERE context_key LIKE ?', ['integtest-%']);
        $this->queryDatabase('DELETE FROM ' . $this->getTableName('context') . ' WHERE `key` LIKE ?', ['integtest-%']);

        parent::tearDown();
    }
}
