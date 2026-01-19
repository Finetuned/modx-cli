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

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array_merge(parent::getOptions(), [
            [
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Force flush without confirmation'
            ],
        ]);
    }

    /**
     * Prepare properties before running the processor.
     *
     * @param array $properties The processor properties.
     * @param array $options    The processor options.
     * @return boolean|null Return false to abort.
     */
    protected function beforeRun(array &$properties = [], array &$options = [])
    {
        // Confirm flush unless --force is used
        if (!$this->option('force')) {
            if (!$this->confirm('Are you sure you want to flush permissions? This will clear all permission caches.')) {
                $this->info('Operation aborted');
                return false;
            }
        }
        return null;
    }

    /**
     * Handle the processor response.
     *
     * @param array $response The processor response.
     * @return integer
     */
    protected function processResponse(array $response = [])
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
