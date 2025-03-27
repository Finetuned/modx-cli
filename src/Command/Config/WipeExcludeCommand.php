<?php namespace MODX\CLI\Command\Config;

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
            $this->info('No commands are excluded');
            return 0;
        }
        
        if (!$this->confirm('Are you sure you want to wipe all excluded commands?')) {
            return $this->info('Operation aborted');
        }
        
        // Wipe all excluded commands
        foreach ($excluded as $class) {
            $excludedCommands->remove($class);
        }
        $excludedCommands->save();
        
        $this->info('All excluded commands wiped');
        
        return 0;
    }
}
