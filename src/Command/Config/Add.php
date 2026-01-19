<?php

namespace MODX\CLI\Command\Config;

use MODX\CLI\Command\BaseCmd;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to add a MODX instance to the configuration
 */
class Add extends BaseCmd
{
    protected $name = 'config:add';
    protected $description = 'Add a MODX instance to the configuration';
    protected $help = 'This command adds a MODX instance to the configuration, allowing you to run commands on it.';

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
                'The name of the instance'
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

        // If no base path is provided, use the current directory
        if (!$basePath) {
            $basePath = $this->getApplication()->getCwd();
        }

        // Make sure the base path ends with a trailing slash
        if (substr($basePath, -1) !== '/') {
            $basePath .= '/';
        }

        // Check if the instance already exists
        $instances = $this->getApplication()->instances;
        if ($instances->get($name)) {
            if (!$this->confirm("Instance '{$name}' already exists. Do you want to overwrite it?")) {
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

        // Add the instance to the configuration
        $instances->set($name, [
            'base_path' => $basePath,
        ]);
        $instances->save();

        // Set as default if requested
        if ($default) {
            $instances->set('__default__', [
                'class' => $name,
            ]);
            $instances->save();
            $message = "Instance '{$name}' added and set as default";
        } else {
            $message = "Instance '{$name}' added";
        }

        if ($this->option('json')) {
            $this->output->writeln(json_encode([
                'success' => true,
                'message' => $message,
                'instance' => [
                    'name' => $name,
                    'base_path' => $basePath,
                    'is_default' => (bool) $default,
                ],
            ], JSON_PRETTY_PRINT));
        } else {
            $this->info($message);
        }

        return 0;
    }
}
