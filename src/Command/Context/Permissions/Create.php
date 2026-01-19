<?php

namespace MODX\CLI\Command\Context\Permissions;

use MODX\CLI\Command\ProcessorCmd;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to create a context access permission
 */
class Create extends ProcessorCmd
{
    protected $processor = 'Security\\Access\\UserGroup\\Context\\Create';

    protected $name = 'context:permissions:create';
    protected $description = 'Create a context access permission';

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            [
                'context',
                InputArgument::REQUIRED,
                'The context key'
            ],
            [
                'usergroup',
                InputArgument::REQUIRED,
                'The user group ID'
            ],
            [
                'policy',
                InputArgument::REQUIRED,
                'The access policy ID'
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
                'authority',
                null,
                InputOption::VALUE_REQUIRED,
                'The authority level',
                0
            ],
        ]);
    }

    /**
     * Prepare properties before running the processor.
     *
     * @param array $properties The processor properties.
     * @param array $options    The processor options.
     * @return void
     */
    protected function beforeRun(array &$properties = [], array &$options = [])
    {
        $properties['target'] = $this->argument('context');
        $properties['principal'] = $this->argument('usergroup');
        $properties['policy'] = $this->argument('policy');
        $properties['authority'] = (int) $this->option('authority');
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
            $this->info('Context access permission created successfully');
            return 0;
        }

        $this->error('Failed to create context access permission');
        if (isset($response['message'])) {
            $this->error($response['message']);
        }
        return 1;
    }
}
