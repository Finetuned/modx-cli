<?php namespace MODX\CLI\Command\Config;

use MODX\CLI\Command\BaseCmd;
use Symfony\Component\Console\Input\InputArgument;

/**
 * A command to remove a MODX instance from the configuration
 */
class Rm extends BaseCmd
{
    protected $name = 'config:rm';
    protected $description = 'Remove a MODX instance from the configuration';

    protected function getArguments()
    {
        return array(
            array(
                'name',
                InputArgument::REQUIRED,
                'The name of the instance to remove'
            ),
        );
    }

    protected function process()
    {
        $name = $this->argument('name');
        $instances = $this->getApplication()->instances;
        
        // Check if the instance exists
        if (!$instances->get($name)) {
            $this->error("Instance '{$name}' does not exist");
            return 1;
        }
        
        // Check if the instance is the default
        $default = $instances->get('__default__');
        if ($default && isset($default['class']) && $default['class'] === $name) {
            if (!$this->confirm("Instance '{$name}' is the default instance. Do you want to remove it?")) {
                return $this->info('Operation aborted');
            }
            
            // Remove the default instance
            $instances->remove('__default__');
        }
        
        // Remove the instance
        $instances->remove($name);
        $instances->save();
        
        $this->info("Instance '{$name}' removed");
        
        return 0;
    }
}
