<?php

namespace MODX\CLI\Command\Ns;

use MODX\CLI\Command\ProcessorCmd;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to remove a namespace in MODX
 */
class Remove extends ProcessorCmd
{
    protected $processor = 'workspace/namespace/remove';
    protected $required = array('id');

    protected $name = 'ns:remove';
    protected $description = 'Remove a namespace in MODX';

    protected function getArguments()
    {
        return array(
            array(
                'id',
                InputArgument::REQUIRED,
                'The ID of the namespace to remove'
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

        // Get the namespace to display information
        $namespace = $this->modx->getObject('modNamespace', $id);
        if (!$namespace) {
            $this->error("Namespace with ID {$id} not found");
            return false;
        }

        $name = $namespace->get('name');

        // Confirm removal unless --force is used
        if (!$this->option('force')) {
            if (!$this->confirm("Are you sure you want to remove namespace '{$name}' (ID: {$id})?")) {
                $this->info('Operation aborted');
                return false;
            }
        }
    }

    protected function processResponse(array $response = array())
    {
        if (isset($response['success']) && $response['success']) {
            $this->info('Namespace removed successfully');
        } else {
            $this->error('Failed to remove namespace');

            if (isset($response['message'])) {
                $this->error($response['message']);
            }
        }
    }
}
