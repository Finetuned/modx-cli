<?php

namespace MODX\CLI\Command\Package\Provider;

use MODX\CLI\Command\ProcessorCmd;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to get information about a package provider in MODX
 */
class Info extends ProcessorCmd
{
    protected $processor = 'Workspace\Providers\GetList';
    protected $required = ['id'];

    /**
     * Prepare properties before running the processor.
     *
     * @param array $properties The processor properties.
     * @param array $options    The processor options.
     * @return void
     */
    protected function beforeRun(array &$properties = [], array &$options = [])
    {
        // Filter by provider ID
        $properties['id'] = $this->argument('id');
    }

    protected $name = 'package:provider:info';
    protected $description = 'Get information about a package provider in MODX';

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            [
                'id',
                InputArgument::REQUIRED,
                'The ID of the provider'
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
                'format',
                'f',
                InputOption::VALUE_REQUIRED,
                'Output format (table, json)',
                'table'
            ],
        ]);
    }

    /**
     * Handle the processor response.
     *
     * @param array $response The processor response.
     * @return integer
     */
    protected function processResponse(array $response = [])
    {
        // GetList returns 'results' array instead of 'object'
        if (!isset($response['results']) || empty($response['results'])) {
            $this->error('Provider not found');
            return 1;
        }

        // Get the first (and only) provider from results
        $provider = $response['results'][0];
        $format = $this->option('format');

        if ($format === 'json' || $this->option('json')) {
            $this->output->writeln(json_encode($provider, JSON_PRETTY_PRINT));
            return 0;
        }

        // Default to table format
        $table = new Table($this->output);
        $table->setHeaders(['Property', 'Value']);

        // Add basic properties
        $properties = [
            'id', 'name', 'description', 'service_url', 'username', 'verified'
        ];

        foreach ($properties as $property) {
            if (isset($provider[$property])) {
                $value = $provider[$property];

                // Format boolean values
                if ($property === 'verified') {
                    $value = $value ? 'Yes' : 'No';
                }

                $table->addRow([$property, $value]);
            }
        }

        $table->render();
        return 0;
    }
}
