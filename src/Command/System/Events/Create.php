<?php

namespace MODX\CLI\Command\System\Events;

use MODX\CLI\Command\ProcessorCmd;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to create a system event in MODX
 */
class Create extends ProcessorCmd
{
    protected $processor = 'System\Event\Create';

    protected $name = 'system:event:create';
    protected $description = 'Create a system event in MODX';

    protected function getArguments()
    {
        return array(
            array(
                'name',
                InputArgument::REQUIRED,
                'The name of the event'
            ),
        );
    }

    protected function getOptions()
    {
        return array_merge(parent::getOptions(), array(
            array(
                'service',
                null,
                InputOption::VALUE_REQUIRED,
                'The service of the event',
                1
            ),
            array(
                'groupname',
                null,
                InputOption::VALUE_REQUIRED,
                'The group name of the event',
                ''
            ),
        ));
    }

    protected function beforeRun(array &$properties = array(), array &$options = array())
    {
        // Add the name to the properties
        $properties['name'] = $this->argument('name');

        // Add options to the properties
        $optionKeys = array('service', 'groupname');

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
            $this->info('Event created successfully');

            if (isset($response['object']) && isset($response['object']['name'])) {
                $this->info('Event name: ' . $response['object']['name']);
            }
            return 0;
        } else {
            $this->error('Failed to create event');

            if (isset($response['message'])) {
                $this->error($response['message']);
            }
            return 1;
        }
    }
}
