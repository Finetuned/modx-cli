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

        if ($this->option('json')) {
            $this->output->writeln(json_encode([
                'total' => count($excluded),
                'results' => array_values($excluded),
            ], JSON_PRETTY_PRINT));
            return 0;
        }

        if (empty($excluded)) {
            $this->info('No commands are excluded');
            return 0;
        }

        $table = new Table($this->output);
        $table->setHeaders(['Class']);

        foreach ($excluded as $class) {
            $table->addRow([$class]);
        }

        $table->render();

        return 0;
    }
}
