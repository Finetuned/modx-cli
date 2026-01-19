<?php

namespace MODX\CLI\Command\Context\Setting;

use MODX\CLI\Command\ProcessorCmd;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to create a context setting
 */
class Create extends ProcessorCmd
{
    protected $processor = 'Context\Setting\Create';

    protected $name = 'context:setting:create';
    protected $description = 'Create a context setting';

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
            [
                'namespace',
                null,
                InputOption::VALUE_REQUIRED,
                'The setting namespace',
                'core'
            ],
            [
                'xtype',
                null,
                InputOption::VALUE_REQUIRED,
                'The setting xtype',
                'textfield'
            ],
            [
                'name',
                null,
                InputOption::VALUE_REQUIRED,
                'The setting name'
            ],
            [
                'description',
                null,
                InputOption::VALUE_REQUIRED,
                'The setting description'
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
        $properties['fk'] = $this->argument('context');
        $properties['context_key'] = $this->argument('context');
        $properties['key'] = $this->argument('key');

        $optionKeys = ['value', 'area', 'namespace', 'xtype', 'name', 'description'];
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
            $this->info('Context setting created successfully');
            return 0;
        }

        $this->error('Failed to create context setting');
        if (isset($response['message'])) {
            $this->error($response['message']);
        }
        return 1;
    }
}
