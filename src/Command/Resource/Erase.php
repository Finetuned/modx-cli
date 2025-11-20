<?php

namespace MODX\CLI\Command\Resource;

use MODX\CLI\Command\ProcessorCmd;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to erase a MODX resource (permanently delete from trash)
 */
class Erase extends ProcessorCmd
{
    protected $processor = 'Resource\Trash\Purge';
    protected $required = array();

    protected $name = 'resource:erase';
    protected $description = 'Erase a MODX resource (permanently delete from trash)';

    protected function getArguments()
    {
        return array(
            array(
                'id',
                InputArgument::REQUIRED,
                'The ID of the resource to erase'
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
                'Force erase without confirmation'
            ),
        ));
    }

    protected function beforeRun(array &$properties = array(), array &$options = array())
    {
        $id = $this->argument('id');

        // Get the resource to display information
        $resource = $this->modx->getObject(\MODX\Revolution\modResource::class, $id);
        if (!$resource) {
            $this->error("Resource with ID {$id} not found");
            return false;
        }

        $pagetitle = $resource->get('pagetitle');
        $isDeleted = $resource->get('deleted');

        // Check if resource is in trash
        if (!$isDeleted) {
            $this->error("Resource '{$pagetitle}' (ID: {$id}) is not in the trash");
            $this->info("Use 'resource:delete' to move it to trash first");
            return false;
        }

        // The processor expects 'ids' parameter (comma-separated list)
        $properties['ids'] = (string)$id;

        // Confirm erase unless --force is used
        if (!$this->option('force')) {
            $this->error('WARNING: This operation is irreversible!');
            if (!$this->confirm("Are you sure you want to permanently erase resource '{$pagetitle}' (ID: {$id}) from trash?")) {
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
            $this->info('Resource erased successfully (permanently deleted)');
            return 0;
        } else {
            $this->error('Failed to erase resource');

            if (isset($response['message'])) {
                $this->error($response['message']);
            }
            return 1;
        }
    }
}
