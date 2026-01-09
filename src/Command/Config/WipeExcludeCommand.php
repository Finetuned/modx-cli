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

    protected function process()
    {
        $excludedCommands = $this->getApplication()->excludedCommands;
        $excluded = $excludedCommands->getAll();

        if (empty($excluded)) {
            $message = 'No commands are excluded';
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

        if (!$this->confirm('Are you sure you want to wipe all excluded commands?')) {
            if ($this->option('json')) {
                $this->output->writeln(json_encode([
                    'success' => false,
                    'message' => 'Operation aborted',
                    'wiped' => false,
                    'total' => count($excluded),
                ], JSON_PRETTY_PRINT));
            } else {
                $this->info('Operation aborted');
            }
            return 0;
        }

        // Wipe all excluded commands
        foreach ($excluded as $class) {
            $excludedCommands->remove($class);
        }
        $excludedCommands->save();

        $message = 'All excluded commands wiped';
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
