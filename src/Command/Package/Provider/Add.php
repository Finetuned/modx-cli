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

    protected function getArguments()
    {
        return array(
            array(
                'name',
                InputArgument::REQUIRED,
                'The name of the provider'
            ),
            array(
                'service_url',
                InputArgument::REQUIRED,
                'The service URL of the provider'
            ),
        );
    }

    protected function getOptions()
    {
        return array_merge(parent::getOptions(), array(
            array(
                'username',
                null,
                InputOption::VALUE_REQUIRED,
                'The username for the provider'
            ),
            array(
                'password',
                null,
                InputOption::VALUE_REQUIRED,
                'The password for the provider'
            ),
            array(
                'description',
                null,
                InputOption::VALUE_REQUIRED,
                'The description of the provider'
            ),
        ));
    }

    protected function beforeRun(array &$properties = array(), array &$options = array())
    {
        // Add the name and service_url to the properties
        $properties['name'] = $this->argument('name');
        $properties['service_url'] = $this->argument('service_url');

        // Add options to the properties
        $optionKeys = array('username', 'password', 'description');

        foreach ($optionKeys as $key) {
            if ($this->option($key) !== null) {
                $properties[$key] = $this->option($key);
            }
        }
    }

    protected function processResponse(array $response = array())
    {
        if (isset($response['success']) && $response['success']) {
            $this->info('Provider added successfully');

            if (isset($response['object']) && isset($response['object']['id'])) {
                $this->info('Provider ID: ' . $response['object']['id']);
            }
        } else {
            $this->error('Failed to add provider');

            if (isset($response['message'])) {
                $this->error($response['message']);
            }
        }
    }
}
