<?php

namespace MODX\CLI\Command\Context\Setting;

use MODX\CLI\Command\ProcessorCmd;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to remove a context setting
 */
class Remove extends ProcessorCmd
{
    protected $processor = 'Context\Setting\Remove';

    protected $name = 'context:setting:remove';
    protected $description = 'Remove a context setting';

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
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Force removal without confirmation'
            ),
        ));
    }

    protected function beforeRun(array &$properties = array(), array &$options = array())
    {
        $context = $this->argument('context');
        $key = $this->argument('key');
        
        $properties['context_key'] = $context;
        $properties['key'] = $key;

        // Ask for confirmation unless --force is used
        if (!$this->option('force')) {
            $confirmed = $this->confirm(
                "Are you sure you want to remove the setting '{$key}' from context '{$context}'?",
                false
            );

            if (!$confirmed) {
                $this->info('Context setting removal cancelled');
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
            $this->info('Context setting removed successfully');
            return 0;
        } else {
            $this->error('Failed to remove context setting');

            if (isset($response['message'])) {
                $this->error($response['message']);
            }
            return 1;
        }
    }
}