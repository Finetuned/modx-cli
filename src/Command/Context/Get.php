<?php

namespace MODX\CLI\Command\Context;

use MODX\CLI\Command\ProcessorCmd;
use Symfony\Component\Console\Input\InputArgument;

/**
 * A command to get a single MODX context
 */
class Get extends ProcessorCmd
{
    protected $processor = 'Context\Get';

    protected $name = 'context:get';
    protected $description = 'Get a MODX context by key';

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
                'The context key'
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
                $context = $response['object'];
                $this->info($this->trans('context.get.context_label', [], 'commands') . $context['key']);
                $this->info($this->trans('context.get.name_label', [], 'commands') . ($context['name'] ?? ''));
                $this->info($this->trans('context.get.description_label', [], 'commands') . ($context['description'] ?? ''));
                $this->info($this->trans('context.get.rank_label', [], 'commands') . ($context['rank'] ?? 0));
            }
            return 0;
        } else {
            $this->error($this->trans('context.get.failed', [], 'commands'));
            if (isset($response['message'])) {
                $this->error($response['message']);
            }
            return 1;
        }
    }
}
