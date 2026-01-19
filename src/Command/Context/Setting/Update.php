<?php

namespace MODX\CLI\Command\Context\Setting;

use MODX\CLI\Command\ProcessorCmd;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to update a context setting
 */
class Update extends ProcessorCmd
{
    protected $processor = 'Context\Setting\Update';

    protected $name = 'context:setting:update';
    protected $description = 'Update a context setting';

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
                'key',
                InputArgument::REQUIRED,
                'The setting key'
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
                'value',
                null,
                InputOption::VALUE_REQUIRED,
                'The setting value'
            ],
            [
                'area',
                null,
                InputOption::VALUE_REQUIRED,
                'The setting area/category'
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
        $properties['context_key'] = $this->argument('context');
        $properties['key'] = $this->argument('key');

        // Pre-populate from existing setting
        $this->prePopulateFromExisting($properties, 'Context\Setting\Get', 'key');

        // Add options to the properties
        $optionKeys = ['value', 'area'];

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
            $this->info('Context setting updated successfully');
            return 0;
        } else {
            $this->error('Failed to update context setting');

            if (isset($response['message'])) {
                $this->error($response['message']);
            }
            return 1;
        }
    }
}
