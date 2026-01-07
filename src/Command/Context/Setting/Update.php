<?php

namespace MODX\CLI\Command\Context\Setting;

use MODX\CLI\Command\ProcessorCmd;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to update a context setting
 */
class Update extends ProcessorCmd
{
    protected $processor = 'Context\Setting\Update';

    protected $name = 'context:setting:update';
    protected $description = 'Update a context setting';

    protected function getArguments()
    {
        return array(
            array(
                'context',
                InputArgument::REQUIRED,
                'The context key'
            ),
            array(
                'key',
                InputArgument::REQUIRED,
                'The setting key'
            ),
        );
    }

    protected function getOptions()
    {
        return array_merge(parent::getOptions(), array(
            array(
                'value',
                null,
                InputOption::VALUE_REQUIRED,
                'The setting value'
            ),
            array(
                'area',
                null,
                InputOption::VALUE_REQUIRED,
                'The setting area/category'
            ),
        ));
    }

    protected function beforeRun(array &$properties = array(), array &$options = array())
    {
        $properties['context_key'] = $this->argument('context');
        $properties['key'] = $this->argument('key');

        // Pre-populate from existing setting
        $this->prePopulateFromExisting($properties, 'Context\Setting\Get', 'key');

        // Add options to the properties
        $optionKeys = array('value', 'area');

        foreach ($optionKeys as $key) {
            if ($this->option($key) !== null) {
                $properties[$key] = $this->option($key);
            }
        }
    }

    protected function processResponse(array $response = array())
    {
        if ($this->option('json')) {
            return parent::processResponse($response);
        }
        
        if (isset($response['success']) && $response['success']) {
            $this->info('Context setting updated successfully');
            return 0;
        } else {
            $this->error('Failed to update context setting');

            if (isset($response['message'])) {
                $this->error($response['message']);
            }
            return 1;
        }
    }
}