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
 * Mock the modX class for coverage analysis
 * 
 * The MODX CLI extends modX in some places (e.g., Xdom class),
 * but when running unit tests or generating coverage reports,
 * the actual MODX framework may not be available.
 * 
 * This mock provides the minimal interface needed for PHP to
 * parse and analyze the code during coverage generation.
 * 
 * Key insight: During integration tests, commands run in separate processes
 * where MODX is properly loaded. However, coverage analysis happens in the
 * main PHPUnit process where MODX is NOT loaded. Therefore, we need the mock
 * for coverage analysis but must allow it to be overridden if MODX loads later.
 * 
 * We only create this mock if the class doesn't already exist.
 */
if (!class_exists('modX') && !class_exists('MODX\\Revolution\\modX')) {
    /**
     * Mock modX class for coverage analysis
     * 
     * This is NOT a functional MODX implementation - it exists
     * solely to allow coverage analysis to work when the actual
     * MODX framework isn't loaded.
     */
    class modX
    {
        /**
         * Mock toJSON method used by Xdom
         */
        public function toJSON($data)
        {
            return json_encode($data);
        }
        
        /**
         * Mock runProcessor method
         */
        public function runProcessor($action, array $scriptProperties = [])
        {
            return [];
        }
        
        /**
         * Mock getVersionData method
         */
        public function getVersionData()
        {
            return [
                'version' => '3.0.0',
                'major_version' => '3',
                'minor_version' => '0',
                'patch_level' => 'pl',
            ];
        }
        
        /**
         * Mock getOption method
         */
        public function getOption($key, $options = null, $default = null)
        {
            return $default;
        }
    }
}
