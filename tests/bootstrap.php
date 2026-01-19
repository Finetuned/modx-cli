<?php

/**
 * PHPUnit Bootstrap File
 *
 * This file is loaded before tests run and provides necessary mocks
 * for coverage analysis.
 */

// Isolate HOME during unit/coverage runs to prevent picking up user MODX instances.
if (!getenv('MODX_INTEGRATION_TESTS')) {
    $tmpHome = sys_get_temp_dir() . '/modx_cli_unit_home';
    if (!is_dir($tmpHome)) {
        mkdir($tmpHome, 0777, true);
    }
    putenv('HOME=' . $tmpHome);
    $_ENV['HOME'] = $tmpHome;
}

// Load the main autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Note: modX stubbing is now handled in specific test files (e.g., XdomTest) to avoid conflicts with integration runs.
