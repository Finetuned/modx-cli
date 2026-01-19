<?php

namespace MODX\CLI\Command\System\Events;

use MODX\CLI\Command\ProcessorCmd;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to delete a system event in MODX
 */
class Delete extends ProcessorCmd
{
    protected $processor = 'System\Event\Remove';
    protected $required = ['id'];

    protected $name = 'system:event:delete';
    protected $description = 'Delete a system event in MODX';

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            [
                'id',
                InputArgument::REQUIRED,
                'The ID of the event'
            ],
        ];
    }

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
                'Force deletion without confirmation'
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
        $id = $this->argument('id');

        // Get the event to display information
        $event = $this->modx->getObject(\MODX\Revolution\modEvent::class, $id);
        if (!$event) {
            $this->error("Event with ID {$id} not found");
            return false;
        }

        $name = $event->get('name');

        // Confirm deletion unless --force is used
        if (!$this->option('force')) {
            if (!$this->confirm("Are you sure you want to delete event '{$name}' (ID: {$id})?")) {
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
            $this->info('Event deleted successfully');
            return 0;
        } else {
            $this->error('Failed to delete event');

            if (isset($response['message'])) {
                $this->error($response['message']);
            }
            return 1;
        }
    }
}
