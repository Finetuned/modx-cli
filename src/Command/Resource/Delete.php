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
    protected $required = ['id'];

    protected $name = 'resource:delete';
    protected $description = 'Delete a MODX resource (move to trash)';

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
                'The ID of the resource to delete'
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

        // Confirm deletion unless --force is used
        if (!$this->option('force')) {
            if (!$this->confirm("Are you sure you want to delete resource '{$pagetitle}' (ID: {$id})?")) {
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
            $this->info('Resource deleted successfully (moved to trash)');
            return 0;
        } else {
            $this->error('Failed to delete resource');

            if (isset($response['message'])) {
                $this->error($response['message']);
            }
            return 1;
        }
    }
}
