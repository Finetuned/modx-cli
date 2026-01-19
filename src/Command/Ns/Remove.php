<?php

namespace MODX\CLI\Command\Ns;

use MODX\CLI\Command\ProcessorCmd;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to remove a namespace in MODX
 */
class Remove extends ProcessorCmd
{
    protected $processor = 'Workspace\PackageNamespace\Remove';
    protected $required = ['name'];

    protected $name = 'ns:remove';
    protected $description = 'Remove a namespace in MODX';

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
                'The name of the namespace to remove'
            ],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array_merge(parent::getOptions(), [
            [
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Force removal without confirmation'
            ],
        ]);
    }

    /**
     * Prepare properties before running the processor.
     *
     * @param array $properties The processor properties.
     * @param array $options    The processor options.
     * @return boolean|null Return false to abort.
     */
    protected function beforeRun(array &$properties = [], array &$options = [])
    {
        $name = $this->argument('name');

        // Add the name argument to properties (it's the primary key)
        $properties['name'] = $name;

        // Get the namespace to display information
        $namespace = $this->modx->getObject(\MODX\Revolution\modNamespace::class, ['name' => $name]);
        if (!$namespace) {
            $this->error("Namespace '{$name}' not found");
            return false;
        }

        // Confirm removal unless --force is used
        if (!$this->option('force')) {
            if (!$this->confirm("Are you sure you want to remove namespace '{$name}'?")) {
                $this->info('Operation aborted');
                return false;
            }
        }
        return null;
    }

    /**
     * Handle the processor response.
     *
     * @param array $response The processor response.
     * @return integer
     */
    protected function processResponse(array $response = [])
    {
        if ($this->option('json')) {
            return parent::processResponse($response);
        }

        if (isset($response['success']) && $response['success']) {
            $this->info('Namespace removed successfully');
            return 0;
        } else {
            $this->error('Failed to remove namespace');

            if (isset($response['message'])) {
                $this->error($response['message']);
            }
            return 1;
        }
    }
}
