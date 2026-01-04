<?php

namespace MODX\CLI\Command\Session;

use MODX\CLI\Command\BaseCmd;
use MODX\Revolution\modActiveUser;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to remove a session in MODX
 */
class Remove extends BaseCmd
{
    const MODX = true;
    
    protected $name = 'session:remove';
    protected $description = 'Remove a session in MODX';

    protected function getArguments()
    {
        return array(
            array(
                'id',
                InputArgument::REQUIRED,
                'The ID (internalKey) of the session to remove'
            ),
        );
    }

    protected function getOptions()
    {
        return array_merge(parent::getOptions(), array(
            array(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Force removal without confirmation'
            ),
        ));
    }

    protected function process()
    {
        $id = $this->argument('id');

        // Get the active user session to display information
        $activeUser = $this->modx->getObject(modActiveUser::class, array('internalKey' => $id));
        if (!$activeUser) {
            $this->error("Session with ID {$id} not found");
            return 1;
        }

        $username = $activeUser->get('username');

        // Confirm removal unless --force is used
        if (!$this->option('force')) {
            $message = "Are you sure you want to remove session '{$id}'";
            if ($username) {
                $message .= " for user '{$username}'";
            }
            $message .= "?";

            if (!$this->confirm($message)) {
                $this->info('Operation aborted');
                return 0;
            }
        }

        // Remove the active user entry
        if ($activeUser->remove()) {
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
