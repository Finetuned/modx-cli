<?php

namespace MODX\CLI\Command\Session;

use MODX\CLI\Command\BaseCmd;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to remove a session in MODX
 */
class Remove extends BaseCmd
{
    public const MODX = true;

    protected $name = 'session:remove';
    protected $description = 'Remove a session in MODX';

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            [
                'id',
                InputArgument::REQUIRED,
                'The ID (internalKey) of the session to remove'
            ],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array_merge(parent::getOptions(), [
            [
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Force removal without confirmation'
            ],
        ]);
    }

    /**
     * Execute the command.
     *
     * @return integer
     */
    protected function process(): int
    {
        $id = $this->argument('id');

        $session = $this->modx->getObject('MODX\\Revolution\\modSession', ['id' => $id]);
        if (!$session) {
            $this->error("Session with ID {$id} not found");
            return 1;
        }

        // Confirm removal unless --force is used
        if (!$this->option('force')) {
            $message = "Are you sure you want to remove session '{$id}'?";
            if (!$this->confirm($message)) {
                $this->info('Operation aborted');
                return 0;
            }
        }

        if ($session->remove()) {
            if ($this->option('json')) {
                $this->output->writeln(json_encode([
                    'success' => true,
                    'message' => 'Session removed successfully'
                ]));
            } else {
                $this->info('Session removed successfully');
            }
            return 0;
        } else {
            if ($this->option('json')) {
                $this->output->writeln(json_encode([
                    'success' => false,
                    'message' => 'Failed to remove session'
                ]));
            } else {
                $this->error('Failed to remove session');
            }
            return 1;
        }
    }
}
