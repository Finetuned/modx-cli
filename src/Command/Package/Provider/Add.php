<?php

namespace MODX\CLI\Command\Package\Provider;

use MODX\CLI\Command\ProcessorCmd;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to add a package provider in MODX
 */
class Add extends ProcessorCmd
{
    protected $processor = 'Workspace\Providers\Create';

    protected $name = 'package:provider:add';
    protected $description = 'Add a package provider in MODX';

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
                'The name of the provider'
            ],
            [
                'service_url',
                InputArgument::REQUIRED,
                'The service URL of the provider'
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
                'username',
                null,
                InputOption::VALUE_REQUIRED,
                'The username for the provider'
            ],
            [
                'password',
                null,
                InputOption::VALUE_REQUIRED,
                'The password for the provider'
            ],
            [
                'description',
                null,
                InputOption::VALUE_REQUIRED,
                'The description of the provider'
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
        // Add the name and service_url to the properties
        $properties['name'] = $this->argument('name');
        $properties['service_url'] = $this->argument('service_url');

        // Add options to the properties
        $optionKeys = ['username', 'password', 'description'];

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
            $this->info('Provider added successfully');

            if (isset($response['object']) && isset($response['object']['id'])) {
                $this->info('Provider ID: ' . $response['object']['id']);
            }
            return 0;
        } else {
            $this->error('Failed to add provider');

            if (isset($response['message'])) {
                $this->error($response['message']);
            }
            return 1;
        }
    }
}
