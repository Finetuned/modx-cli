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
    protected $processor = 'Workspace\PackageNamespace\Remove';
    protected $required = array('name');

    protected $name = 'ns:remove';
    protected $description = 'Remove a namespace in MODX';

    protected function getArguments()
    {
        return array(
            array(
                'name',
                InputArgument::REQUIRED,
                'The name of the namespace to remove'
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
        $name = $this->argument('name');
        
        // Add the name argument to properties (it's the primary key)
        $properties['name'] = $name;

        // Get the namespace to display information
        $namespace = $this->modx->getObject('modNamespace', array('name' => $name));
        if (!$namespace) {
            $this->error("Namespace '{$name}' not found");
            return false;
        }

        // Confirm removal unless --force is used
        if (!$this->option('force')) {
            if (!$this->confirm("Are you sure you want to remove namespace '{$name}'?")) {
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
