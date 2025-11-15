<?php

namespace MODX\CLI\Command\Resource;

use MODX\CLI\Command\ProcessorCmd;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to delete a MODX resource (move to trash)
 */
class Delete extends ProcessorCmd
{
    protected $processor = 'Resource\Delete';
    protected $required = array('id');

    protected $name = 'resource:delete';
    protected $description = 'Delete a MODX resource (move to trash)';

    protected function getArguments()
    {
        return array(
            array(
                'id',
                InputArgument::REQUIRED,
                'The ID of the resource to delete'
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

        // Get the resource to display information
        $resource = $this->modx->getObject('modResource', $id);
        if (!$resource) {
            $this->error("Resource with ID {$id} not found");
            return false;
        }

        $pagetitle = $resource->get('pagetitle');

        // Confirm deletion unless --force is used
        if (!$this->option('force')) {
            if (!$this->confirm("Are you sure you want to delete resource '{$pagetitle}' (ID: {$id})?")) {
                $this->info('Operation aborted');
                return false;
            }
        }
    }

    protected function processResponse(array $response = array())
    {
        if (isset($response['success']) && $response['success']) {
            $this->info('Resource deleted successfully (moved to trash)');
        } else {
            $this->error('Failed to delete resource');

            if (isset($response['message'])) {
                $this->error($response['message']);
            }
        }
    }
}
