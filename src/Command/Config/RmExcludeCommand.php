<?php namespace MODX\CLI\Command\Config;

use MODX\CLI\Command\BaseCmd;
use Symfony\Component\Console\Input\InputArgument;

/**
 * A command to remove a command from the excluded commands
 */
class RmExcludeCommand extends BaseCmd
{
    protected $name = 'config:rm-exclude-command';
    protected $description = 'Remove a command from the excluded commands';

    protected function getArguments()
    {
        return array(
            array(
                'class',
                InputArgument::REQUIRED,
                'The command class to remove from the excluded commands'
            ),
        );
    }

    protected function process()
    {
        $class = $this->argument('class');
        $excludedCommands = $this->getApplication()->excludedCommands;
        
        // Check if the command is excluded
        $excluded = $excludedCommands->getAll();
        if (!in_array($class, $excluded)) {
            $this->info("Command '{$class}' is not excluded");
            return 0;
        }
        
        // Remove the command from the excluded commands
        $excludedCommands->remove($class);
        $excludedCommands->save();
        
        $this->info("Command '{$class}' removed from excluded commands");
        
        return 0;
    }
}
