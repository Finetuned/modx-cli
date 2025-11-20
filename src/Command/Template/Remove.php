<?php

namespace MODX\CLI\Command\Template;

use MODX\CLI\Command\ProcessorCmd;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to remove a MODX template
 */
class Remove extends ProcessorCmd
{
    protected $processor = 'Element\Template\Remove';
    protected $required = array('id');

    protected $name = 'template:remove';
    protected $description = 'Remove a MODX template';

    protected function getArguments()
    {
        return array(
            array(
                'id',
                InputArgument::REQUIRED,
                'The ID of the template to remove'
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

        // Get the template to display information
        $template = $this->modx->getObject(\MODX\Revolution\modTemplate::class, $id);
        if (!$template) {
            $this->error("Template with ID {$id} not found");
            return false;
        }

        $templateName = $template->get('templatename');

        // Confirm removal unless --force is used
        if (!$this->option('force')) {
            if (!$this->confirm("Are you sure you want to remove template '{$templateName}' (ID: {$id})?")) {
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
            $this->info('Template removed successfully');
            return 0;
        } else {
            $this->error('Failed to remove template');

            if (isset($response['message'])) {
                $this->error($response['message']);
            }
            return 1;
        }
    }
}
