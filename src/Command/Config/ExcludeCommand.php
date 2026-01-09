<?php

namespace MODX\CLI\Command\Config;

use MODX\CLI\Command\BaseCmd;
use Symfony\Component\Console\Input\InputArgument;

/**
 * A command to exclude a command from the available commands
 */
class ExcludeCommand extends BaseCmd
{
    protected $name = 'config:exclude-command';
    protected $description = 'Exclude a command from the available commands';

    protected function getArguments()
    {
        return array(
            array(
                'class',
                InputArgument::REQUIRED,
                'The command class to exclude'
            ),
        );
    }

    protected function process()
    {
        $class = $this->argument('class');
        $excludedCommands = $this->getApplication()->excludedCommands;

        // Check if the command is already excluded
        $excluded = $excludedCommands->getAll();
        if (in_array($class, $excluded)) {
            $message = "Command '{$class}' is already excluded";
            if ($this->option('json')) {
                $this->output->writeln(json_encode([
                    'success' => true,
                    'message' => $message,
                    'command' => $class,
                    'excluded' => true,
                ], JSON_PRETTY_PRINT));
            } else {
                $this->info($message);
            }
            return 0;
        }

        // Exclude the command
        $excludedCommands->set($class);
        $excludedCommands->save();

        $message = "Command '{$class}' excluded";
        if ($this->option('json')) {
            $this->output->writeln(json_encode([
                'success' => true,
                'message' => $message,
                'command' => $class,
                'excluded' => true,
            ], JSON_PRETTY_PRINT));
        } else {
            $this->info($message);
        }

        return 0;
    }
}
