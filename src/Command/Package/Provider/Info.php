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
    protected $required = array('id');

    protected function beforeRun(array &$properties = array(), array &$options = array())
    {
        // Filter by provider ID
        $properties['id'] = $this->argument('id');
    }

    protected $name = 'package:provider:info';
    protected $description = 'Get information about a package provider in MODX';

    protected function getArguments()
    {
        return array(
            array(
                'id',
                InputArgument::REQUIRED,
                'The ID of the provider'
            ),
        );
    }

    protected function getOptions()
    {
        return array_merge(parent::getOptions(), array(
            array(
                'format',
                'f',
                InputOption::VALUE_REQUIRED,
                'Output format (table, json)',
                'table'
            ),
        ));
    }

    protected function processResponse(array $response = array())
    {
        // GetList returns 'results' array instead of 'object'
        if (!isset($response['results']) || empty($response['results'])) {
            $this->error('Provider not found');
            return 1;
        }

        // Get the first (and only) provider from results
        $provider = $response['results'][0];
        $format = $this->option('format');

        if ($format === 'json') {
            $this->output->writeln(json_encode($provider, JSON_PRETTY_PRINT));
            return;
        }

        // Default to table format
        $table = new Table($this->output);
        $table->setHeaders(array('Property', 'Value'));

        // Add basic properties
        $properties = array(
            'id', 'name', 'description', 'service_url', 'username', 'verified'
        );

        foreach ($properties as $property) {
            if (isset($provider[$property])) {
                $value = $provider[$property];

                // Format boolean values
                if ($property === 'verified') {
                    $value = $value ? 'Yes' : 'No';
                }

                $table->addRow(array($property, $value));
            }
        }

        $table->render();
    }
}
