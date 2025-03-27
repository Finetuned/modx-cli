<?php namespace MODX\CLI\Command\System\Events;

use MODX\CLI\Command\ProcessorCmd;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to delete a system event in MODX
 */
class Delete extends ProcessorCmd
{
    protected $processor = 'system/event/remove';
    protected $required = array('id');

    protected $name = 'system:event:delete';
    protected $description = 'Delete a system event in MODX';

    protected function getArguments()
    {
        return array(
            array(
                'id',
                InputArgument::REQUIRED,
                'The ID of the event'
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
                'Force deletion without confirmation'
            ),
        ));
    }

    protected function beforeRun(array &$properties = array(), array &$options = array())
    {
        $id = $this->argument('id');
        
        // Get the event to display information
        $event = $this->modx->getObject('modEvent', $id);
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
    }

    protected function processResponse(array $response = array())
    {
        if (isset($response['success']) && $response['success']) {
            $this->info('Event deleted successfully');
        } else {
            $this->error('Failed to delete event');
            
            if (isset($response['message'])) {
                $this->error($response['message']);
            }
        }
    }
}
