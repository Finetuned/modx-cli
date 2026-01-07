<?php

namespace MODX\CLI\Command\Context;

use MODX\CLI\Command\ProcessorCmd;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to update a MODX context
 */
class Update extends ProcessorCmd
{
    protected $processor = 'Context\Update';

    protected $name = 'context:update';
    protected $description = 'Update a MODX context';

    protected function getArguments()
    {
        return array(
            array(
                'key',
                InputArgument::REQUIRED,
                'The context key'
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
                'The name of the context'
            ),
            array(
                'description',
                null,
                InputOption::VALUE_REQUIRED,
                'The description of the context'
            ),
            array(
                'rank',
                null,
                InputOption::VALUE_REQUIRED,
                'The rank/order of the context'
            ),
        ));
    }

    protected function beforeRun(array &$properties = array(), array &$options = array())
    {
        // Add the key to the properties
        $properties['key'] = $this->argument('key');

        // Pre-populate from existing context
        $this->prePopulateFromExisting($properties, 'Context\Get', 'key');

        // Add options to the properties
        $optionKeys = array('name', 'description', 'rank');

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
            $this->info('Context updated successfully');

            if (isset($response['object']) && isset($response['object']['key'])) {
                $this->info('Context key: ' . $response['object']['key']);
            }
            return 0;
        } else {
            $this->error('Failed to update context');

            if (isset($response['message'])) {
                $this->error($response['message']);
            }
            return 1;
        }
    }
}