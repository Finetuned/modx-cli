<?php namespace MODX\CLI\Command\Session;

use MODX\CLI\Command\ProcessorCmd;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to remove a session in MODX
 */
class Remove extends ProcessorCmd
{
    protected $processor = 'security/session/remove';
    protected $required = array('id');

    protected $name = 'session:remove';
    protected $description = 'Remove a session in MODX';

    protected function getArguments()
    {
        return array(
            array(
                'id',
                InputArgument::REQUIRED,
                'The ID of the session to remove'
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

    protected function beforeRun(array &$properties = array(), array &$options = array())
    {
        $id = $this->argument('id');
        
        // Get the session to display information
        $session = $this->modx->getObject('modSession', $id);
        if (!$session) {
            $this->error("Session with ID {$id} not found");
            return false;
        }
        
        // Get the user information
        $username = '';
        $userId = $session->get('user');
        if ($userId) {
            $user = $this->modx->getObject('modUser', $userId);
            if ($user) {
                $username = $user->get('username');
            }
        }
        
        // Confirm removal unless --force is used
        if (!$this->option('force')) {
            $message = "Are you sure you want to remove session '{$id}'";
            if ($username) {
                $message .= " for user '{$username}'";
            }
            $message .= "?";
            
            if (!$this->confirm($message)) {
                $this->info('Operation aborted');
                return false;
            }
        }
    }

    protected function processResponse(array $response = array())
    {
        if (isset($response['success']) && $response['success']) {
            $this->info('Session removed successfully');
        } else {
            $this->error('Failed to remove session');
            
            if (isset($response['message'])) {
                $this->error($response['message']);
            }
        }
    }
}
