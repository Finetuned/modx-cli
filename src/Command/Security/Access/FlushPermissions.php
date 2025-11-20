<?php

namespace MODX\CLI\Command\Security\Access;

use MODX\CLI\Command\ProcessorCmd;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to flush permissions in MODX
 */
class FlushPermissions extends ProcessorCmd
{
    protected $processor = 'Security\Access\Flush';

    protected $name = 'security:access:flush';
    protected $description = 'Flush permissions in MODX';

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
            if (!$this->confirm('Are you sure you want to flush permissions? This will clear all permission caches.')) {
                $this->info('Operation aborted');
                return false;
            }
        }
    }

    protected function processResponse(array $response = array())
    {
        if ($this->option('json')) {
            return parent::processResponse($response);
        }

        if (isset($response['success']) && $response['success']) {
            $this->info('Permissions flushed successfully');
            return 0;
        } else {
            $this->error('Failed to flush permissions');

            if (isset($response['message'])) {
                $this->error($response['message']);
            }
            return 1;
        }
    }
}
