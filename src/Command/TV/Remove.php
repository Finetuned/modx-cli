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
    protected $required = ['id'];

    protected $name = 'tv:remove';
    protected $description = 'Remove a MODX template variable';

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
                'The ID of the template variable to remove'
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

        // Get the template variable to display information
        $tv = $this->modx->getObject(\MODX\Revolution\modTemplateVar::class, $id);
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
            $this->info('Template variable removed successfully');
            return 0;
        } else {
            $this->error('Failed to remove template variable');

            if (isset($response['message'])) {
                $this->error($response['message']);
            }
            return 1;
        }
    }
}
