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
    protected $required = [];

    protected $name = 'resource:erase';
    protected $description = 'Erase a MODX resource (permanently delete from trash)';

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
                'The ID of the resource to erase'
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
                'Force erase without confirmation'
            ],
        ]);
    }

    /**
     * Prepare processor properties before execution.
     *
     * @param array $properties The processor properties.
     * @param array $options    The processor options.
     * @return boolean|null False to abort execution, otherwise null.
     */
    protected function beforeRun(array &$properties = [], array &$options = [])
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
            $message = "Are you sure you want to permanently erase resource '{$pagetitle}' (ID: {$id}) from trash?";
            if (!$this->confirm($message)) {
                $this->info('Operation aborted');
                return false;
            }
        }
        return null;
    }

    /**
     * Process processor response.
     *
     * @param array $response The decoded processor response.
     * @return integer
     */
    protected function processResponse(array $response = [])
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
