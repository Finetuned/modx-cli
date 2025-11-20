<?php

/**
 * PHPUnit Bootstrap File
 * 
 * This file is loaded before tests run and provides necessary mocks
 * for coverage analysis.
 */

// Load the main autoloader
require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Provide a lightweight global modX for unit/coverage runs only when a real
 * MODX instance is not expected (integration runs set MODX_INTEGRATION_TESTS=1).
 */
if (!getenv('MODX_INTEGRATION_TESTS') && !class_exists('modX')) {
    class modX
    {
        public function toJSON($data)
        {
            return json_encode($data);
        }

        public function runProcessor($action, array $scriptProperties = [])
        {
            return [];
        }

        public function getVersionData()
        {
            return [
                'version' => '3.0.0',
                'major_version' => '3',
                'minor_version' => '0',
                'patch_level' => 'pl',
            ];
        }

        public function getOption($key, $options = null, $default = null)
        {
            return $default;
        }
    }
}

// Provide a namespaced alias to prevent autoloading the real MODX class when the stub is in use during unit runs.
if (!getenv('MODX_INTEGRATION_TESTS') && class_exists('modX') && !class_exists('MODX\\Revolution\\modX')) {
    class_alias('modX', 'MODX\\Revolution\\modX');
}
