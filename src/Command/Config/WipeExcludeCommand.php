<?php

namespace MODX\CLI\Command\Config;

use MODX\CLI\Command\BaseCmd;

/**
 * A command to wipe all excluded commands
 */
class WipeExcludeCommand extends BaseCmd
{
    protected $name = 'config:wipe-exclude-command';
    protected $description = 'Wipe all excluded commands';

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
        $excludedCommands = $this->getApplication()->excludedCommands;
        $excluded = $excludedCommands->getAll();

        if (empty($excluded)) {
            $message = $this->trans('config.wipeexcludecommand.none_excluded', [], 'commands');
            if ($this->option('json')) {
                $this->output->writeln(json_encode([
                    'success' => true,
                    'message' => $message,
                    'wiped' => false,
                    'total' => 0,
                ], JSON_PRETTY_PRINT));
            } else {
                $this->info($message);
            }
            return 0;
        }

        if (!$this->confirm($this->trans('config.wipeexcludecommand.confirm', [], 'commands'))) {
            if ($this->option('json')) {
                $this->output->writeln(json_encode([
                    'success' => false,
                    'message' => $this->trans('operation_aborted', [], 'errors'),
                    'wiped' => false,
                    'total' => count($excluded),
                ], JSON_PRETTY_PRINT));
            } else {
                $this->info($this->trans('operation_aborted', [], 'errors'));
            }
            return 0;
        }

        // Wipe all excluded commands
        foreach ($excluded as $class) {
            $excludedCommands->remove($class);
        }
        $excludedCommands->save();

        $message = $this->trans('config.wipeexcludecommand.wiped', [], 'commands');
        if ($this->option('json')) {
            $this->output->writeln(json_encode([
                'success' => true,
                'message' => $message,
                'wiped' => true,
                'total' => count($excluded),
            ], JSON_PRETTY_PRINT));
        } else {
            $this->info($message);
        }

        return 0;
    }
}
