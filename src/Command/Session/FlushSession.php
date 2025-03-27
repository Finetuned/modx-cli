<?php

namespace MODX\CLI\Command\Session;

use MODX\CLI\Command\ProcessorCmd;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to flush all sessions in MODX
 */
class FlushSession extends ProcessorCmd
{
    protected $processor = 'security/flush';

    protected $name = 'session:flush';
    protected $description = 'Flush all sessions in MODX';

    protected function getOptions()
    {
        return array_merge(parent::getOptions(), array(
            array(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Force flush without confirmation'
            ),
        ));
    }

    protected function beforeRun(array &$properties = array(), array &$options = array())
    {
        // Confirm flush unless --force is used
        if (!$this->option('force')) {
            if (!$this->confirm('Are you sure you want to flush all sessions? This will log out all users.')) {
                $this->info('Operation aborted');
                return false;
            }
        }
    }

    protected function processResponse(array $response = array())
    {
        if (isset($response['success']) && $response['success']) {
            $this->info('Sessions flushed successfully');
        } else {
            $this->error('Failed to flush sessions');

            if (isset($response['message'])) {
                $this->error($response['message']);
            }
        }
    }
}
