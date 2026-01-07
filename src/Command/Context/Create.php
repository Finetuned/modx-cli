<?php

namespace MODX\CLI\Command\Context;

use MODX\CLI\Command\ProcessorCmd;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to create a MODX context
 */
class Create extends ProcessorCmd
{
    protected $processor = 'Context\Create';

    protected $name = 'context:create';
    protected $description = 'Create a MODX context';

    protected function getArguments()
    {
        return array(
            array(
                'key',
                InputArgument::REQUIRED,
                'The context key (unique identifier)'
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
                'The name of the context',
                ''
            ),
            array(
                'description',
                null,
                InputOption::VALUE_REQUIRED,
                'The description of the context',
                ''
            ),
            array(
                'rank',
                null,
                InputOption::VALUE_REQUIRED,
                'The rank/order of the context',
                0
            ),
        ));
    }

    protected function beforeRun(array &$properties = array(), array &$options = array())
    {
        // Add the key to the properties
        $properties['key'] = $this->argument('key');

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
            $this->info('Context created successfully');

            if (isset($response['object']) && isset($response['object']['key'])) {
                $this->info('Context key: ' . $response['object']['key']);
            }
            return 0;
        } else {
            $this->error('Failed to create context');

            if (isset($response['message'])) {
                $this->error($response['message']);
            }
            return 1;
        }
    }
}