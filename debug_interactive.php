#!/usr/bin/env php
<?php
/**
 * Interactive Debug Wrapper for MODX CLI
 * 
 * This wrapper allows dynamic debugging of any MODX CLI command without
 * hardcoding arguments in VS Code launch.json. It provides an interactive
 * prompt for command input when debugging.
 */

if (PHP_SAPI !== 'cli') {
    echo 'Warning: MODX CLI should be invoked via the CLI version of PHP, not the '.PHP_SAPI.' SAPI'.PHP_EOL;
    exit(1);
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

/**
 * Get command input dynamically
 */
function getCommandInput() {
    // Check if arguments were passed directly (for non-interactive use)
    global $argv;
    if (count($argv) > 1) {
        return array_slice($argv, 1);
    }
    
    // Check for environment variable (allows setting command in VS Code terminal)
    $envCommand = getenv('MODX_DEBUG_COMMAND');
    if ($envCommand) {
        echo "🐛 Debug Mode: Running command from environment: $envCommand\n";
        return explode(' ', $envCommand);
    }
    
    // Interactive mode - prompt for command
    echo "\n";
    echo "🐛 MODX CLI Interactive Debugger\n";
    echo "================================\n";
    echo "Set breakpoints in VS Code, then enter a command to debug.\n";
    echo "Examples:\n";
    echo "  package:list\n";
    echo "  package:upgradeable\n";
    echo "  package:list-remote\n";
    echo "  tv:update --name=test --description='Test TV' 1\n";
    echo "  resource:create --pagetitle='Test Page' --content='Hello World'\n";
    echo "\n";
    
    // Read command from stdin
    echo "Enter MODX CLI command: ";
    $handle = fopen("php://stdin", "r");
    $command = trim(fgets($handle));
    fclose($handle);
    
    if (empty($command)) {
        echo "No command entered. Exiting.\n";
        exit(0);
    }
    
    echo "🐛 Debug Mode: Running command: $command\n";
    echo "Set your breakpoints and step through the code!\n";
    echo str_repeat("-", 50) . "\n";
    
    // Parse command string into arguments
    return parseCommandString($command);
}

/**
 * Parse command string into arguments array
 * Handles quoted arguments properly
 */
function parseCommandString($command) {
    $args = [];
    $current = '';
    $inQuotes = false;
    $quoteChar = '';
    
    for ($i = 0; $i < strlen($command); $i++) {
        $char = $command[$i];
        
        if (($char === '"' || $char === "'") && !$inQuotes) {
            $inQuotes = true;
            $quoteChar = $char;
        } elseif ($char === $quoteChar && $inQuotes) {
            $inQuotes = false;
            $quoteChar = '';
        } elseif ($char === ' ' && !$inQuotes) {
            if ($current !== '') {
                $args[] = $current;
                $current = '';
            }
        } else {
            $current .= $char;
        }
    }
    
    if ($current !== '') {
        $args[] = $current;
    }
    
    return $args;
}

/**
 * Display helpful debugging information
 */
function displayDebugInfo($args) {
    echo "\n🔍 Debug Information:\n";
    echo "Command: " . implode(' ', $args) . "\n";
    echo "Arguments count: " . count($args) . "\n";
    echo "Working directory: " . getcwd() . "\n";
    echo "PHP version: " . PHP_VERSION . "\n";
    
    if (extension_loaded('xdebug')) {
        echo "Xdebug: ✅ Loaded (version " . phpversion('xdebug') . ")\n";
        if (function_exists('xdebug_is_debugger_active')) {
            echo "Debugger active: " . (xdebug_is_debugger_active() ? "✅ Yes" : "❌ No") . "\n";
        }
    } else {
        echo "Xdebug: ❌ Not loaded\n";
    }
    echo str_repeat("-", 50) . "\n\n";
}

// Main execution
try {
    // Get command arguments dynamically
    $args = getCommandInput();
    
    if (empty($args)) {
        echo "No command specified. Exiting.\n";
        exit(0);
    }
    
    // Display debug information
    displayDebugInfo($args);
    
    // Create StringInput from arguments
    $inputString = implode(' ', $args);
    $input = new StringInput($inputString);
    
    // Run the command application with StringInput
    $application = new Application();
    $exitCode = $application->run($input);
    
    echo "\n🐛 Debug session completed with exit code: $exitCode\n";
    exit($exitCode);
    
} catch (Exception $e) {
    echo "\n❌ Error during debug session:\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
} catch (Throwable $e) {
    echo "\n❌ Fatal error during debug session:\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
