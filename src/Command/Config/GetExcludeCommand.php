<?php

namespace MODX\CLI\Command\Config;

use MODX\CLI\Command\BaseCmd;
use Symfony\Component\Console\Helper\Table;

/**
 * A command to get the list of excluded commands
 */
class GetExcludeCommand extends BaseCmd
{
    protected $name = 'config:get-exclude-command';
    protected $description = 'Get the list of excluded commands';

    protected function process()
    {
        $excludedCommands = $this->getApplication()->excludedCommands;
        $excluded = $excludedCommands->getAll();

        if (empty($excluded)) {
            $this->info('No commands are excluded');
            return 0;
        }

        $table = new Table($this->output);
        $table->setHeaders(array('Class'));

        foreach ($excluded as $class) {
            $table->addRow(array($class));
        }

        $table->render();

        return 0;
    }
}
