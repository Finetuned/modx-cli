<?php

namespace MODX\CLI\Command\Resource;

use MODX\CLI\Command\ProcessorCmd;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to purge a MODX resource
 */
class Purge extends ProcessorCmd
{
    protected $processor = 'resource/purge';
    protected $required = array('id');

    protected $name = 'resource:purge';
    protected $description = 'Purge a MODX resource (permanently delete)';

    protected function getArguments()
    {
        return array(
            array(
                'id',
                InputArgument::REQUIRED,
                'The ID of the resource to purge'
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
                'Force purge without confirmation'
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

        // Confirm purge unless --force is used
        if (!$this->option('force')) {
            $this->error('WARNING: This operation is irreversible!');
            if (!$this->confirm("Are you sure you want to permanently delete resource '{$pagetitle}' (ID: {$id})?")) {
                $this->info('Operation aborted');
                return false;
            }
        }
    }

    protected function processResponse(array $response = array())
    {
        if (isset($response['success']) && $response['success']) {
            $this->info('Resource purged successfully');
        } else {
            $this->error('Failed to purge resource');

            if (isset($response['message'])) {
                $this->error($response['message']);
            }
        }
    }
}
