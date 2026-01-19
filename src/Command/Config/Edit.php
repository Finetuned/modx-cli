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

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            [
                'name',
                InputArgument::REQUIRED,
                'The name of the instance to edit'
            ],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array_merge(parent::getOptions(), [
            [
                'base_path',
                null,
                InputOption::VALUE_REQUIRED,
                'The base path of the MODX instance'
            ],
            [
                'default',
                null,
                InputOption::VALUE_NONE,
                'Set this instance as the default'
            ],
        ]);
    }

    /**
     * Execute the command.
     *
     * @return integer
     */
    /**
     * Execute the command.
     *
     * @return integer
     */
    protected function process()
    {
        $name = $this->argument('name');
        $basePath = $this->option('base_path');
        $default = $this->option('default');

        $instances = $this->getApplication()->instances;

        // Check if the instance exists
        $instance = $instances->get($name);
        if (!$instance) {
            if ($this->option('json')) {
                $this->output->writeln(json_encode([
                    'success' => false,
                    'message' => "Instance '{$name}' does not exist",
                ], JSON_PRETTY_PRINT));
            } else {
                $this->error("Instance '{$name}' does not exist");
            }
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
                    if ($this->option('json')) {
                        $this->output->writeln(json_encode([
                            'success' => false,
                            'message' => 'Operation aborted',
                        ], JSON_PRETTY_PRINT));
                    } else {
                        $this->info('Operation aborted');
                    }
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
            $instances->set('__default__', [
                'class' => $name,
            ]);
            $instances->save();
            $message = "Instance '{$name}' updated and set as default";
        } else {
            $message = "Instance '{$name}' updated";
        }

        if ($this->option('json')) {
            $this->output->writeln(json_encode([
                'success' => true,
                'message' => $message,
                'instance' => [
                    'name' => $name,
                    'base_path' => isset($instance['base_path']) ? $instance['base_path'] : null,
                    'is_default' => (bool) $default,
                ],
            ], JSON_PRETTY_PRINT));
        } else {
            $this->info($message);
        }

        return 0;
    }
}
