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
    protected $required = ['id'];

    protected $name = 'template:remove';
    protected $description = 'Remove a MODX template';

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
                'The ID of the template to remove'
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
                'Force removal without confirmation'
            ],
        ]);
    }

    /**
     * Prepare properties before running the processor.
     *
     * @param array $properties The processor properties.
     * @param array $options    The processor options.
     * @return boolean|null Return false to abort.
     */
    protected function beforeRun(array &$properties = [], array &$options = [])
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
        return null;
    }

    /**
     * Handle the processor response.
     *
     * @param array $response The processor response.
     * @return integer
     */
    protected function processResponse(array $response = [])
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
