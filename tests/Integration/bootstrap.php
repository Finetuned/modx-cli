<?php

/**
 * Integration Test Bootstrap File
 * 
 * This file is loaded before integration tests run and ensures proper MODX loading
 * without any stub interference.
 */

// Integration tests MUST NOT use temporary HOME or load stubs
// We need the real user environment to find MODX configurations

// Load the main autoloader
require_once __DIR__ . '/../../vendor/autoload.php';

// Load environment variables from .env file if present
if (file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
    foreach ($_ENV as $key => $value) {
        if (getenv($key) === false) {
            putenv($key . '=' . $value);
        }
    }
}

// DO NOT stub modX - integration tests require real MODX class
// The modX class will be loaded by MODX CMS when tests initialize MODX instances
