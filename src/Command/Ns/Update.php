<?php

namespace MODX\CLI\Command\Ns;

use MODX\CLI\Command\ProcessorCmd;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to update a namespace in MODX
 */
class Update extends ProcessorCmd
{
    protected $processor = 'Workspace\PackageNamespace\Update';
    protected $required = ['name'];

    protected $name = 'ns:update';
    protected $description = 'Update a namespace in MODX';

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
                'The name of the namespace to update'
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
                'path',
                null,
                InputOption::VALUE_REQUIRED,
                'The path of the namespace'
            ],
            [
                'assets_path',
                null,
                InputOption::VALUE_REQUIRED,
                'The assets path of the namespace'
            ],
        ]);
    }

    /**
     * Prepare properties before running the processor.
     *
     * @param array $properties The processor properties.
     * @param array $options    The processor options.
     * @return void
     */
    protected function beforeRun(array &$properties = [], array &$options = [])
    {
        // Add the name argument to properties (it's the primary key)
        $properties['name'] = $this->argument('name');

        // Add options to the properties
        $optionKeys = ['path', 'assets_path'];

        foreach ($optionKeys as $key) {
            if ($this->option($key) !== null) {
                $properties[$key] = $this->option($key);
            }
        }
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
            $this->info('Namespace updated successfully');

            if (isset($response['object']) && isset($response['object']['id'])) {
                $this->info('Namespace ID: ' . $response['object']['id']);
            }
            return 0;
        } else {
            $this->error('Failed to update namespace');

            if (isset($response['message'])) {
                $this->error($response['message']);
            }
            return 1;
        }
    }
}
