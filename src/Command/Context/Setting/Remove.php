<?php

namespace MODX\CLI\Command\Context\Setting;

use MODX\CLI\Command\ProcessorCmd;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to remove a context setting
 */
class Remove extends ProcessorCmd
{
    protected $processor = 'Context\Setting\Remove';

    protected $name = 'context:setting:remove';
    protected $description = 'Remove a context setting';

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
     * @return void
     */
    protected function beforeRun(array &$properties = [], array &$options = [])
    {
        $context = $this->argument('context');
        $key = $this->argument('key');

        $properties['context_key'] = $context;
        $properties['key'] = $key;

        // Ask for confirmation unless --force is used
        if (!$this->option('force')) {
            $confirmed = $this->confirm(
                $this->trans('context.setting.remove.confirm', ['%key%' => $key, '%context%' => $context], 'commands'),
                false
            );

            if (!$confirmed) {
                $this->info($this->trans('context.setting.remove.cancelled', [], 'commands'));
                exit(0);
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
            $this->info($this->trans('context.setting.remove.success', [], 'commands'));
            return 0;
        } else {
            $this->error($this->trans('context.setting.remove.failed', [], 'commands'));

            if (isset($response['message'])) {
                $this->error($response['message']);
            }
            return 1;
        }
    }
}
