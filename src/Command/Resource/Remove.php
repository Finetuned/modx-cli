<?php namespace MODX\CLI\Command\Resource;

use MODX\CLI\Command\ProcessorCmd;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to remove a MODX resource
 */
class Remove extends ProcessorCmd
{
    protected $processor = 'resource/delete';
    protected $required = array('id');

    protected $name = 'resource:remove';
    protected $description = 'Remove a MODX resource';

    protected function getArguments()
    {
        return array(
            array(
                'id',
                InputArgument::REQUIRED,
                'The ID of the resource to remove'
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
        $id = $this->argument('id');
        
        // Get the resource to display information
        $resource = $this->modx->getObject('modResource', $id);
        if (!$resource) {
            $this->error("Resource with ID {$id} not found");
            return false;
        }
        
        $pagetitle = $resource->get('pagetitle');
        
        // Confirm removal unless --force is used
        if (!$this->option('force')) {
            if (!$this->confirm("Are you sure you want to remove resource '{$pagetitle}' (ID: {$id})?")) {
                $this->info('Operation aborted');
                return false;
            }
        }
    }

    protected function processResponse(array $response = array())
    {
        if (isset($response['success']) && $response['success']) {
            $this->info('Resource removed successfully');
        } else {
            $this->error('Failed to remove resource');
            
            if (isset($response['message'])) {
                $this->error($response['message']);
            }
        }
    }
}
