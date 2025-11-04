<?php

namespace MODX\CLI\Command\TV;

use MODX\CLI\Command\ProcessorCmd;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to remove a MODX template variable
 */
class Remove extends ProcessorCmd
{
    protected $processor = 'Element\Tv\Remove';
    protected $required = array('id');

    protected $name = 'tv:remove';
    protected $description = 'Remove a MODX template variable';

    protected function getArguments()
    {
        return array(
            array(
                'id',
                InputArgument::REQUIRED,
                'The ID of the template variable to remove'
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

        // Get the template variable to display information
        $tv = $this->modx->getObject('modTemplateVar', $id);
        if (!$tv) {
            $this->error("Template variable with ID {$id} not found");
            return false;
        }

        $tvName = $tv->get('name');

        // Confirm removal unless --force is used
        if (!$this->option('force')) {
            if (!$this->confirm("Are you sure you want to remove template variable '{$tvName}' (ID: {$id})?")) {
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
            $this->info('Template variable removed successfully');
        } else {
            $this->error('Failed to remove template variable');

            if (isset($response['message'])) {
                $this->error($response['message']);
            }
        }
    }
}
