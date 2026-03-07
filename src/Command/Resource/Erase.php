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
            $this->error($this->trans('resource_not_found', ['%id%' => $id], 'errors'));
            return false;
        }

        $pagetitle = $resource->get('pagetitle');
        $isDeleted = $resource->get('deleted');

        // Check if resource is in trash
        if (!$isDeleted) {
            $this->error($this->trans('resource.erase.not_in_trash', ['%pagetitle%' => $pagetitle, '%id%' => $id], 'commands'));
            $this->info($this->trans('resource.erase.use_delete_hint', [], 'commands'));
            return false;
        }

        // The processor expects 'ids' parameter (comma-separated list)
        $properties['ids'] = (string)$id;

        // Confirm erase unless --force is used
        if (!$this->option('force')) {
            $this->error($this->trans('resource.erase.irreversible_warning', [], 'commands'));
            $message = "Are you sure you want to permanently erase resource '{$pagetitle}' (ID: {$id}) from trash?";
            if (!$this->confirm($message)) {
                $this->info($this->trans('operation_aborted', [], 'errors'));
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
            $this->info($this->trans('resource.erase.success', [], 'commands'));
            return 0;
        } else {
            $this->error($this->trans('resource.erase.failed', [], 'commands'));

            if (isset($response['message'])) {
                $this->error($response['message']);
            }
            return 1;
        }
    }
}
