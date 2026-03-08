<?php

namespace MODX\CLI\Command\Config;

use MODX\CLI\Command\BaseCmd;
use Symfony\Component\Console\Input\InputArgument;

/**
 * A command to rename a MODX instance in the configuration
 */
class Rename extends BaseCmd
{
    protected $name = 'config:rename';
    protected $description = 'Rename a MODX instance in the configuration';

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
                'old_name',
                InputArgument::REQUIRED,
                'The current name of the instance'
            ],
            [
                'new_name',
                InputArgument::REQUIRED,
                'The new name of the instance'
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
        $oldName = $this->argument('old_name');
        $newName = $this->argument('new_name');

        $instances = $this->getApplication()->instances;

        // Check if the old instance exists
        $instance = $instances->get($oldName);
        if (!$instance) {
            if ($this->option('json')) {
                $this->output->writeln(json_encode([
                    'success' => false,
                    'message' => $this->trans('config.rename.old_not_found', ['%name%' => $oldName], 'commands'),
                ], JSON_PRETTY_PRINT));
            } else {
                $this->error($this->trans('config.rename.old_not_found', ['%name%' => $oldName], 'commands'));
            }
            return 1;
        }

        // Check if the new instance already exists
        if ($instances->get($newName)) {
            if (!$this->confirm($this->trans('config.rename.exists_confirm', ['%name%' => $newName], 'commands'))) {
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
        }

        // Check if the old instance is the default
        $default = $instances->get('__default__');
        $isDefault = ($default && isset($default['class']) && $default['class'] === $oldName);

        // Remove the old instance
        $instances->remove($oldName);

        // Add the new instance
        $instances->set($newName, $instance);

        // Update the default instance if needed
        if ($isDefault) {
            $instances->set('__default__', [
                'class' => $newName,
            ]);
        }

        $instances->save();

        if ($isDefault) {
            $message = $this->trans('config.rename.renamed_default', ['%oldName%' => $oldName, '%newName%' => $newName], 'commands');
        } else {
            $message = $this->trans('config.rename.renamed', ['%oldName%' => $oldName, '%newName%' => $newName], 'commands');
        }

        if ($this->option('json')) {
            $this->output->writeln(json_encode([
                'success' => true,
                'message' => $message,
                'instance' => [
                    'old_name' => $oldName,
                    'new_name' => $newName,
                    'is_default' => $isDefault,
                ],
            ], JSON_PRETTY_PRINT));
        } else {
            $this->info($message);
        }

        return 0;
    }
}
