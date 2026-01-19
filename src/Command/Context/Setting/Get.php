<?php

namespace MODX\CLI\Command\Context\Setting;

use MODX\CLI\Command\ProcessorCmd;
use Symfony\Component\Console\Input\InputArgument;

/**
 * A command to get a single context setting
 */
class Get extends ProcessorCmd
{
    protected $processor = 'Context\Setting\Get';

    protected $name = 'context:setting:get';
    protected $description = 'Get a context setting';

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
            if (isset($response['object'])) {
                $setting = $response['object'];
                $this->info('Context: ' . ($setting['context_key'] ?? ''));
                $this->info('Key: ' . ($setting['key'] ?? ''));
                $this->info('Value: ' . ($setting['value'] ?? ''));
                $this->info('Area: ' . ($setting['area'] ?? ''));
            }
            return 0;
        } else {
            $this->error('Failed to get context setting');
            if (isset($response['message'])) {
                $this->error($response['message']);
            }
            return 1;
        }
    }
}
