<?php

namespace MODX\CLI\Command\Context\Permissions;

use MODX\CLI\Command\ProcessorCmd;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to create a context access permission
 */
class Create extends ProcessorCmd
{
    protected $processor = 'Security\\Access\\UserGroup\\Context\\Create';

    protected $name = 'context:permissions:create';
    protected $description = 'Create a context access permission';

    protected function getArguments()
    {
        return array(
            array(
                'context',
                InputArgument::REQUIRED,
                'The context key'
            ),
            array(
                'usergroup',
                InputArgument::REQUIRED,
                'The user group ID'
            ),
            array(
                'policy',
                InputArgument::REQUIRED,
                'The access policy ID'
            ),
        );
    }

    protected function getOptions()
    {
        return array_merge(parent::getOptions(), array(
            array(
                'authority',
                null,
                InputOption::VALUE_REQUIRED,
                'The authority level',
                0
            ),
        ));
    }

    protected function beforeRun(array &$properties = array(), array &$options = array())
    {
        $properties['target'] = $this->argument('context');
        $properties['principal'] = $this->argument('usergroup');
        $properties['policy'] = $this->argument('policy');
        $properties['authority'] = (int) $this->option('authority');
    }

    protected function processResponse(array $response = array())
    {
        if ($this->option('json')) {
            return parent::processResponse($response);
        }

        if (isset($response['success']) && $response['success']) {
            $this->info('Context access permission created successfully');
            return 0;
        }

        $this->error('Failed to create context access permission');
        if (isset($response['message'])) {
            $this->error($response['message']);
        }
        return 1;
    }
}
