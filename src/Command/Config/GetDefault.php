<?php

namespace MODX\CLI\Command\Config;

use MODX\CLI\Command\BaseCmd;

/**
 * A command to get the default MODX instance
 */
class GetDefault extends BaseCmd
{
    protected $name = 'config:get-default';
    protected $description = 'Get the default MODX instance';

    protected function process()
    {
        $instances = $this->getApplication()->instances;

        // Check if there is a default instance
        $default = $instances->get('__default__');
        if (!$default) {
            if ($this->option('json')) {
                $this->output->writeln(json_encode([
                    'success' => false,
                    'message' => 'No default instance set',
                ], JSON_PRETTY_PRINT));
            } else {
                $this->info('No default instance set');
            }
            return 0;
        }

        // Get the default instance name
        $defaultName = isset($default['class']) ? $default['class'] : null;

        if (!$defaultName) {
            if ($this->option('json')) {
                $this->output->writeln(json_encode([
                    'success' => false,
                    'message' => 'Default instance is set but has no name',
                ], JSON_PRETTY_PRINT));
            } else {
                $this->info('Default instance is set but has no name');
            }
            return 0;
        }

        // Get the default instance configuration
        $defaultConfig = $instances->get($defaultName);
        if (!$defaultConfig) {
            if ($this->option('json')) {
                $this->output->writeln(json_encode([
                    'success' => false,
                    'message' => "Default instance '{$defaultName}' does not exist",
                ], JSON_PRETTY_PRINT));
            } else {
                $this->info("Default instance '{$defaultName}' does not exist");
            }
            return 0;
        }

        // Display the default instance
        if ($this->option('json')) {
            $this->output->writeln(json_encode([
                'success' => true,
                'default' => [
                    'name' => $defaultName,
                    'base_path' => isset($defaultConfig['base_path']) ? $defaultConfig['base_path'] : null,
                ],
            ], JSON_PRETTY_PRINT));
        } else {
            $this->info("Default instance: {$defaultName}");

            if (isset($defaultConfig['base_path'])) {
                $this->info("Base path: {$defaultConfig['base_path']}");
            }
        }

        return 0;
    }
}
