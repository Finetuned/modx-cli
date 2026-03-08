<?php

namespace MODX\CLI\Command\Config;

use MODX\CLI\Command\BaseCmd;

/**
 * A command to get the default MODX instance
 */
class GetDefault extends BaseCmd
{
    protected $name = 'config:get-default';
    protected $description = 'Get the default MODX instance';

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
        $instances = $this->getApplication()->instances;

        // Check if there is a default instance
        $default = $instances->get('__default__');
        if (!$default) {
            if ($this->option('json')) {
                $this->output->writeln(json_encode([
                    'success' => false,
                    'message' => $this->trans('config.getdefault.not_set', [], 'commands'),
                ], JSON_PRETTY_PRINT));
            } else {
                $this->info($this->trans('config.getdefault.not_set', [], 'commands'));
            }
            return 0;
        }

        // Get the default instance name
        $defaultName = isset($default['class']) ? $default['class'] : null;

        if (!$defaultName) {
            if ($this->option('json')) {
                $this->output->writeln(json_encode([
                    'success' => false,
                    'message' => $this->trans('config.getdefault.no_name', [], 'commands'),
                ], JSON_PRETTY_PRINT));
            } else {
                $this->info($this->trans('config.getdefault.no_name', [], 'commands'));
            }
            return 0;
        }

        // Get the default instance configuration
        $defaultConfig = $instances->get($defaultName);
        if (!$defaultConfig) {
            if ($this->option('json')) {
                $this->output->writeln(json_encode([
                    'success' => false,
                    'message' => $this->trans('config.getdefault.not_found', ['%name%' => $defaultName], 'commands'),
                ], JSON_PRETTY_PRINT));
            } else {
                $this->info($this->trans('config.getdefault.not_found', ['%name%' => $defaultName], 'commands'));
            }
            return 0;
        }

        // Display the default instance
        if ($this->option('json')) {
            $this->output->writeln(json_encode([
                'success' => true,
                'default' => [
                    'name' => $defaultName,
                    'base_path' => isset($defaultConfig['base_path']) ? $defaultConfig['base_path'] : null,
                ],
            ], JSON_PRETTY_PRINT));
        } else {
            $this->info($this->trans('config.getdefault.info_name', ['%name%' => $defaultName], 'commands'));

            if (isset($defaultConfig['base_path'])) {
                $this->info($this->trans('config.getdefault.info_path', ['%path%' => $defaultConfig['base_path']], 'commands'));
            }
        }

        return 0;
    }
}
