<?php

namespace MODX\CLI\Command\Config;

use MODX\CLI\Command\BaseCmd;
use Symfony\Component\Console\Input\InputArgument;

/**
 * A command to remove a MODX instance from the configuration
 */
class Rm extends BaseCmd
{
    protected $name = 'config:rm';
    protected $description = 'Remove a MODX instance from the configuration';

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            [
                'name',
                InputArgument::REQUIRED,
                'The name of the instance to remove'
            ],
        ];
    }

    /**
     * Execute the command.
     *
     * @return integer
     */
    /**
     * Execute the command.
     *
     * @return integer
     */
    protected function process()
    {
        $name = $this->argument('name');
        $instances = $this->getApplication()->instances;

        // Check if the instance exists
        if (!$instances->get($name)) {
            if ($this->option('json')) {
                $this->output->writeln(json_encode([
                    'success' => false,
                    'message' => $this->trans('config.rm.not_found', ['%name%' => $name], 'commands'),
                ], JSON_PRETTY_PRINT));
            } else {
                $this->error($this->trans('config.rm.not_found', ['%name%' => $name], 'commands'));
            }
            return 1;
        }

        // Check if the instance is the default
        $default = $instances->get('__default__');
        if ($default && isset($default['class']) && $default['class'] === $name) {
            if (!$this->confirm($this->trans('config.rm.is_default_confirm', ['%name%' => $name], 'commands'))) {
                if ($this->option('json')) {
                    $this->output->writeln(json_encode([
                        'success' => false,
                        'message' => $this->trans('operation_aborted', [], 'errors'),
                    ], JSON_PRETTY_PRINT));
                } else {
                    $this->info($this->trans('operation_aborted', [], 'errors'));
                }
                return 0;
            }

            // Remove the default instance
            $instances->remove('__default__');
        }

        // Remove the instance
        $instances->remove($name);
        $instances->save();

        $message = $this->trans('config.rm.removed', ['%name%' => $name], 'commands');
        if ($this->option('json')) {
            $this->output->writeln(json_encode([
                'success' => true,
                'message' => $message,
                'instance' => [
                    'name' => $name,
                    'was_default' => (bool) ($default && isset($default['class']) && $default['class'] === $name),
                ],
            ], JSON_PRETTY_PRINT));
        } else {
            $this->info($message);
        }

        return 0;
    }
}
