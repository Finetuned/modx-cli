<?php

namespace MODX\CLI\Command\Config;

use MODX\CLI\Command\BaseCmd;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to edit a MODX instance in the configuration
 */
class Edit extends BaseCmd
{
    protected $name = 'config:edit';
    protected $description = 'Edit a MODX instance in the configuration';

    protected function getArguments()
    {
        return array(
            array(
                'name',
                InputArgument::REQUIRED,
                'The name of the instance to edit'
            ),
        );
    }

    protected function getOptions()
    {
        return array(
            array(
                'base_path',
                null,
                InputOption::VALUE_REQUIRED,
                'The base path of the MODX instance'
            ),
            array(
                'default',
                null,
                InputOption::VALUE_NONE,
                'Set this instance as the default'
            ),
        );
    }

    protected function process()
    {
        $name = $this->argument('name');
        $basePath = $this->option('base_path');
        $default = $this->option('default');

        $instances = $this->getApplication()->instances;

        // Check if the instance exists
        $instance = $instances->get($name);
        if (!$instance) {
            $this->error("Instance '{$name}' does not exist");
            return 1;
        }

        // Update the instance
        if ($basePath) {
            // Make sure the base path ends with a trailing slash
            if (substr($basePath, -1) !== '/') {
                $basePath .= '/';
            }

            // Check if the MODX instance exists at the given path
            if (!file_exists($basePath . 'config.core.php')) {
                if (!$this->confirm("No MODX instance found at '{$basePath}'. Do you want to continue?")) {
                    $this->info('Operation aborted');
                    return 0;
                }
            }

            $instance['base_path'] = $basePath;
        }

        // Update the instance
        $instances->set($name, $instance);
        $instances->save();

        // Set as default if requested
        if ($default) {
            $instances->set('__default__', array(
                'class' => $name,
            ));
            $instances->save();
            $this->info("Instance '{$name}' updated and set as default");
        } else {
            $this->info("Instance '{$name}' updated");
        }

        return 0;
    }
}
