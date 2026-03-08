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
                $this->info($this->trans('context.setting.get.context_label', [], 'commands') . ($setting['context_key'] ?? ''));
                $this->info($this->trans('context.setting.get.key_label', [], 'commands') . ($setting['key'] ?? ''));
                $this->info($this->trans('context.setting.get.value_label', [], 'commands') . ($setting['value'] ?? ''));
                $this->info($this->trans('context.setting.get.area_label', [], 'commands') . ($setting['area'] ?? ''));
            }
            return 0;
        } else {
            $this->error($this->trans('context.setting.get.failed', [], 'commands'));
            if (isset($response['message'])) {
                $this->error($response['message']);
            }
            return 1;
        }
    }
}
