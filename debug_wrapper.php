#!/usr/bin/env php
<?php
// debug_wrapper.php

if (PHP_SAPI !== 'cli') {
    echo 'Warning: MODX CLI should be invoked via the CLI version of PHP, not the '.PHP_SAPI.' SAPI'.PHP_EOL;
}

require __DIR__.'/src/bootstrap.php';

use MODX\CLI\Application;
use Symfony\Component\Console\Input\StringInput;

error_reporting(-1);

if (function_exists('ini_set')) {
    @ini_set('display_errors', 1);

    $memoryInBytes = function ($value) {
        $unit = strtolower(substr($value, -1, 1));
        $value = (int) $value;
        switch($unit) {
            case 'g':
                $value *= 1024;
                // no break (cumulative multiplier)
            case 'm':
                $value *= 1024;
                // no break (cumulative multiplier)
            case 'k':
                $value *= 1024;
        }

        return $value;
    };

    $memoryLimit = trim(ini_get('memory_limit'));
    // Increase memory_limit if it is lower than 512M
    if ($memoryLimit != -1 && $memoryInBytes($memoryLimit) < 512 * 1024 * 1024) {
        @ini_set('memory_limit', '512M');
    }
    unset($memoryInBytes, $memoryLimit);
}

// Get all arguments except the script name and create StringInput
$args = array_slice($argv, 1);
$inputString = implode(' ', $args);
$input = new StringInput($inputString);

// run the command application with StringInput instead of default ArgvInput
$application = new Application();
$application->run($input);