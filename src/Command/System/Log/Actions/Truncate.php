<?php

namespace MODX\CLI\Command\System\Log\Actions;

use MODX\CLI\Command\ProcessorCmd;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to truncate action logs in MODX
 */
class Truncate extends ProcessorCmd
{
    protected $processor = 'System\Log\Truncate';

    protected $name = 'system:log:actions:truncate';
    protected $description = 'Truncate action logs in MODX';

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
                'Force truncation without confirmation'
            ],
            [
                'age',
                null,
                InputOption::VALUE_REQUIRED,
                'Truncate logs older than this many days',
                0
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
        // Add the age to the properties
        $age = (int) $this->option('age');
        if ($age > 0) {
            $properties['age'] = $age;
        }

        // Confirm truncation unless --force is used
        if (!$this->option('force')) {
            $message = 'Are you sure you want to truncate all action logs?';
            if ($age > 0) {
                $message = "Are you sure you want to truncate action logs older than {$age} days?";
            }

            if (!$this->confirm($message)) {
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
            $this->info('Action logs truncated successfully');

            if (isset($response['total'])) {
                $this->info('Total logs removed: ' . $response['total']);
            }
            return 0;
        } else {
            $this->error('Failed to truncate action logs');

            if (isset($response['message'])) {
                $this->error($response['message']);
            }
            return 1;
        }
    }
}
