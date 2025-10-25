<?php

namespace MODX\CLI\Command\Ns;

use MODX\CLI\Command\ProcessorCmd;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to create a namespace in MODX
 */
class Create extends ProcessorCmd
{
    protected $processor = 'Workspace\PackageNamespace\Create';

    protected $name = 'ns:create';
    protected $description = 'Create a namespace in MODX';

    protected function getArguments()
    {
        return array(
            array(
                'name',
                InputArgument::REQUIRED,
                'The name of the namespace'
            ),
        );
    }

    protected function getOptions()
    {
        return array_merge(parent::getOptions(), array(
            array(
                'path',
                null,
                InputOption::VALUE_REQUIRED,
                'The path of the namespace'
            ),
            array(
                'assets_path',
                null,
                InputOption::VALUE_REQUIRED,
                'The assets path of the namespace'
            ),
        ));
    }

    protected function beforeRun(array &$properties = array(), array &$options = array())
    {
        // Add the name to the properties
        $properties['name'] = $this->argument('name');

        // Add options to the properties
        $optionKeys = array('path', 'assets_path');

        foreach ($optionKeys as $key) {
            if ($this->option($key) !== null) {
                $properties[$key] = $this->option($key);
            }
        }
    }

    protected function processResponse(array $response = array())
    {
        if (isset($response['success']) && $response['success']) {
            $this->info('Namespace created successfully');

            if (isset($response['object']) && isset($response['object']['id'])) {
                $this->info('Namespace ID: ' . $response['object']['id']);
            }
        } else {
            $this->error('Failed to create namespace');

            if (isset($response['message'])) {
                $this->error($response['message']);
            }
        }
    }
}
