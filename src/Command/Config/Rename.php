<?php

namespace MODX\CLI\Command\Config;

use MODX\CLI\Command\BaseCmd;
use Symfony\Component\Console\Input\InputArgument;

/**
 * A command to rename a MODX instance in the configuration
 */
class Rename extends BaseCmd
{
    protected $name = 'config:rename';
    protected $description = 'Rename a MODX instance in the configuration';

    protected function getArguments()
    {
        return array(
            array(
                'old_name',
                InputArgument::REQUIRED,
                'The current name of the instance'
            ),
            array(
                'new_name',
                InputArgument::REQUIRED,
                'The new name of the instance'
            ),
        );
    }

    protected function process()
    {
        $oldName = $this->argument('old_name');
        $newName = $this->argument('new_name');

        $instances = $this->getApplication()->instances;

        // Check if the old instance exists
        $instance = $instances->get($oldName);
        if (!$instance) {
            $this->error("Instance '{$oldName}' does not exist");
            return 1;
        }

        // Check if the new instance already exists
        if ($instances->get($newName)) {
            if (!$this->confirm("Instance '{$newName}' already exists. Do you want to overwrite it?")) {
                return $this->info('Operation aborted');
            }
        }

        // Check if the old instance is the default
        $default = $instances->get('__default__');
        $isDefault = ($default && isset($default['class']) && $default['class'] === $oldName);

        // Remove the old instance
        $instances->remove($oldName);

        // Add the new instance
        $instances->set($newName, $instance);

        // Update the default instance if needed
        if ($isDefault) {
            $instances->set('__default__', array(
                'class' => $newName,
            ));
        }

        $instances->save();

        if ($isDefault) {
            $this->info("Instance '{$oldName}' renamed to '{$newName}' and set as default");
        } else {
            $this->info("Instance '{$oldName}' renamed to '{$newName}'");
        }

        return 0;
    }
}
