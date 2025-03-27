<?php namespace MODX\CLI\Command\Config;

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
            $this->info('No default instance set');
            return 0;
        }
        
        // Get the default instance name
        $defaultName = isset($default['class']) ? $default['class'] : null;
        
        if (!$defaultName) {
            $this->info('Default instance is set but has no name');
            return 0;
        }
        
        // Get the default instance configuration
        $defaultConfig = $instances->get($defaultName);
        if (!$defaultConfig) {
            $this->info("Default instance '{$defaultName}' does not exist");
            return 0;
        }
        
        // Display the default instance
        $this->info("Default instance: {$defaultName}");
        
        if (isset($defaultConfig['base_path'])) {
            $this->info("Base path: {$defaultConfig['base_path']}");
        }
        
        return 0;
    }
}
