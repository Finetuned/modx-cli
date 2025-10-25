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
    protected $required = array('id');

    protected $name = 'ns:update';
    protected $description = 'Update a namespace in MODX';

    protected function getArguments()
    {
        return array(
            array(
                'id',
                InputArgument::REQUIRED,
                'The ID of the namespace to update'
            ),
        );
    }

    protected function getOptions()
    {
        return array_merge(parent::getOptions(), array(
            array(
                'name',
                null,
                InputOption::VALUE_REQUIRED,
                'The name of the namespace'
            ),
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
        // Add options to the properties
        $optionKeys = array('name', 'path', 'assets_path');

        foreach ($optionKeys as $key) {
            if ($this->option($key) !== null) {
                $properties[$key] = $this->option($key);
            }
        }
    }

    protected function processResponse(array $response = array())
    {
        if (isset($response['success']) && $response['success']) {
            $this->info('Namespace updated successfully');

            if (isset($response['object']) && isset($response['object']['id'])) {
                $this->info('Namespace ID: ' . $response['object']['id']);
            }
        } else {
            $this->error('Failed to update namespace');

            if (isset($response['message'])) {
                $this->error($response['message']);
            }
        }
    }
}
