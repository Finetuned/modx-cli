<?php

namespace MODX\CLI\Command\Context;

use MODX\CLI\Command\ProcessorCmd;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to create a MODX context
 */
class Create extends ProcessorCmd
{
    protected $processor = 'Context\Create';

    protected $name = 'context:create';
    protected $description = 'Create a MODX context';

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            [
                'key',
                InputArgument::REQUIRED,
                'The context key (unique identifier)'
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
                'name',
                null,
                InputOption::VALUE_REQUIRED,
                'The name of the context',
                ''
            ],
            [
                'description',
                null,
                InputOption::VALUE_REQUIRED,
                'The description of the context',
                ''
            ],
            [
                'rank',
                null,
                InputOption::VALUE_REQUIRED,
                'The rank/order of the context',
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
        // Add the key to the properties
        $properties['key'] = $this->argument('key');

        // Add options to the properties
        $optionKeys = ['name', 'description', 'rank'];

        foreach ($optionKeys as $key) {
            if ($this->option($key) !== null) {
                $properties[$key] = $this->option($key);
            }
        }
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
            $this->info('Context created successfully');

            if (isset($response['object']) && isset($response['object']['key'])) {
                $this->info('Context key: ' . $response['object']['key']);
            }
            return 0;
        } else {
            $this->error('Failed to create context');

            if (isset($response['message'])) {
                $this->error($response['message']);
            }
            return 1;
        }
    }
}
