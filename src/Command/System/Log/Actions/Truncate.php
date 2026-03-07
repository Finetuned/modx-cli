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
            if ($age > 0) {
                $message = $this->trans('system.log.actions.truncate.confirm_age', ['%age%' => $age], 'commands');
            } else {
                $message = $this->trans('system.log.actions.truncate.confirm_all', [], 'commands');
            }

            if (!$this->confirm($message)) {
                $this->info($this->trans('operation_aborted', [], 'errors'));
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
            $this->info($this->trans('system.log.actions.truncate.success', [], 'commands'));

            if (isset($response['total'])) {
                $this->info($this->trans('system.log.actions.truncate.count_label', [], 'commands') . $response['total']);
            }
            return 0;
        } else {
            $this->error($this->trans('system.log.actions.truncate.failed', [], 'commands'));

            if (isset($response['message'])) {
                $this->error($response['message']);
            }
            return 1;
        }
    }
}
