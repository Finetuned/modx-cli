<?php

namespace MODX\CLI\Command\Context;

use MODX\CLI\Command\ProcessorCmd;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to remove a MODX context
 */
class Remove extends ProcessorCmd
{
    protected $processor = 'Context\Remove';

    protected $name = 'context:remove';
    protected $description = 'Remove a MODX context';

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
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Force removal without confirmation'
            ),
        ));
    }

    protected function beforeRun(array &$properties = array(), array &$options = array())
    {
        $key = $this->argument('key');
        $properties['key'] = $key;

        // Ask for confirmation unless --force is used
        if (!$this->option('force')) {
            $confirmed = $this->confirm(
                "Are you sure you want to remove the context '{$key}'?",
                false
            );

            if (!$confirmed) {
                $this->info('Context removal cancelled');
                exit(0);
            }
        }
    }

    protected function processResponse(array $response = array())
    {
        if ($this->option('json')) {
            return parent::processResponse($response);
        }
        
        if (isset($response['success']) && $response['success']) {
            $this->info('Context removed successfully');
            return 0;
        } else {
            $this->error('Failed to remove context');

            if (isset($response['message'])) {
                $this->error($response['message']);
            }
            return 1;
        }
    }
}