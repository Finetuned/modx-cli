<?php

function includeIfExists($file)
{
    if (file_exists($file)) {
        return include $file;
    }
}

if ((!$loader = includeIfExists(__DIR__ . '/../vendor/autoload.php')) && (!$loader = includeIfExists(__DIR__ . '/../../../autoload.php'))) {
    echo 'You must set up the project dependencies, run the following commands:' . PHP_EOL .
        'curl -sS https://getcomposer.org/installer | php' . PHP_EOL .
        'php composer.phar install' . PHP_EOL;
    exit(1);
}

// Load custom commands from YAML configuration
loadCustomCommands();

return $loader;

/**
 * Load custom commands from the custom-commands directory
 */
function loadCustomCommands()
{
    $customCommandsDir = __DIR__ . '/../custom-commands';
    $configFile = $customCommandsDir . '/config.yml';
    
    // Check if custom commands config exists
    if (!file_exists($configFile)) {
        return;
    }
    
    // Parse YAML config using Symfony YAML component
    try {
        if (class_exists('Symfony\Component\Yaml\Yaml')) {
            $config = \Symfony\Component\Yaml\Yaml::parseFile($configFile);
        } else {
            // Skip custom commands if Symfony YAML not available
            return;
        }
        
        if (!$config || !isset($config['custom_commands'])) {
            return;
        }
    } catch (Exception $e) {
        // Skip custom commands if YAML parsing fails
        return;
    }
    
    // Load each command group
    foreach ($config['custom_commands'] as $groupName => $groupConfig) {
        if (!isset($groupConfig['functions_file']) || !isset($groupConfig['commands'])) {
            continue;
        }
        
        // Load the functions file
        $functionsFile = $customCommandsDir . '/' . $groupConfig['functions_file'];
        if (file_exists($functionsFile)) {
            require_once $functionsFile;
        }
        
        // Register each command
        foreach ($groupConfig['commands'] as $commandName => $commandConfig) {
            if (!isset($commandConfig['function'])) {
                continue;
            }
            
            $functionName = $commandConfig['function'];
            $description = $commandConfig['description'] ?? '';
            
            // Prepare command arguments for registration
            $commandArgs = [
                'shortdesc' => $description,
                'longdesc' => $description,
            ];
            
            // Add arguments configuration if present
            if (isset($commandConfig['arguments'])) {
                $commandArgs['arguments'] = $commandConfig['arguments'];
            }
            
            // Add options configuration if present
            if (isset($commandConfig['options'])) {
                $commandArgs['options'] = $commandConfig['options'];
            }
            
            // Register the command with the internal API
            if (class_exists('MODX\CLI\API\MODX_CLI')) {
                \MODX\CLI\API\MODX_CLI::add_command($commandName, $functionName, $commandArgs);
            }
        }
    }
}
