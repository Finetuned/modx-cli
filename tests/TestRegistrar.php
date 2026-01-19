<?php

namespace MODX\CLI\Tests;

use MODX\CLI\CommandRegistrar;

/**
 * Concrete implementation of CommandRegistrar for testing
 */
class TestRegistrar extends CommandRegistrar
{
    protected static $commandsFolder = 'Command';

    /**
     * Override getRootPath to use our test directory
     */
    protected static function getRootPath()
    {
        // Get the test directory from the test case
        $testDir = sys_get_temp_dir();
        $dirs = glob($testDir . '/modx_cli_test_commands_*');

        if (!empty($dirs)) {
            rsort($dirs, SORT_STRING);
            return $dirs[0];
        }

        // Fallback to tests directory
        return dirname(__DIR__) . '/tests';
    }
}
