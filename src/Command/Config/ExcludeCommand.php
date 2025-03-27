<?php namespace MODX\CLI\Command\Config;

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
            $this->info("Command '{$class}' is already excluded");
            return 0;
        }
        
        // Exclude the command
        $excludedCommands->set($class);
        $excludedCommands->save();
        
        $this->info("Command '{$class}' excluded");
        
        return 0;
    }
}
