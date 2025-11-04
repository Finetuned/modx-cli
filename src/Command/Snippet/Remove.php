<?php

namespace MODX\CLI\Command\Snippet;

use MODX\CLI\Command\ProcessorCmd;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to remove a MODX snippet
 */
class Remove extends ProcessorCmd
{
    protected $processor = 'Element\Snippet\Remove';
    protected $required = array('id');

    protected $name = 'snippet:remove';
    protected $description = 'Remove a MODX snippet';

    protected function getArguments()
    {
        return array(
            array(
                'id',
                InputArgument::REQUIRED,
                'The ID of the snippet to remove'
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

        // Get the snippet to display information
        $snippet = $this->modx->getObject('modSnippet', $id);
        if (!$snippet) {
            $this->error("Snippet with ID {$id} not found");
            return false;
        }

        $snippetName = $snippet->get('name');

        // Confirm removal unless --force is used
        if (!$this->option('force')) {
            if (!$this->confirm("Are you sure you want to remove snippet '{$snippetName}' (ID: {$id})?")) {
                $this->info('Operation aborted');
                return false;
            }
        }
    }

    protected function processResponse(array $response = array())
    {
        if ($this->option('json') || $this->option('format') === 'json') {
            return parent::processResponse($response);
        }
        
        if (isset($response['success']) && $response['success']) {
            $this->info('Snippet removed successfully');
        } else {
            $this->error('Failed to remove snippet');

            if (isset($response['message'])) {
                $this->error($response['message']);
            }
        }
    }
}
