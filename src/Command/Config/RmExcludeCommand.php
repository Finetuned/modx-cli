<?php

namespace MODX\CLI\Command\Config;

use MODX\CLI\Command\BaseCmd;
use Symfony\Component\Console\Input\InputArgument;

/**
 * A command to remove a command from the excluded commands
 */
class RmExcludeCommand extends BaseCmd
{
    protected $name = 'config:rm-exclude-command';
    protected $description = 'Remove a command from the excluded commands';

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
                'class',
                InputArgument::REQUIRED,
                'The command class to remove from the excluded commands'
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
        $class = $this->argument('class');
        $excludedCommands = $this->getApplication()->excludedCommands;

        // Check if the command is excluded
        $excluded = $excludedCommands->getAll();
        if (!in_array($class, $excluded)) {
            $message = "Command '{$class}' is not excluded";
            if ($this->option('json')) {
                $this->output->writeln(json_encode([
                    'success' => true,
                    'message' => $message,
                    'command' => $class,
                    'removed' => false,
                ], JSON_PRETTY_PRINT));
            } else {
                $this->info($message);
            }
            return 0;
        }

        // Remove the command from the excluded commands
        $excludedCommands->remove($class);
        $excludedCommands->save();

        $message = "Command '{$class}' removed from excluded commands";
        if ($this->option('json')) {
            $this->output->writeln(json_encode([
                'success' => true,
                'message' => $message,
                'command' => $class,
                'removed' => true,
            ], JSON_PRETTY_PRINT));
        } else {
            $this->info($message);
        }

        return 0;
    }
}
