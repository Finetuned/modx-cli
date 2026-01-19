<?php

namespace MODX\CLI\Command\User;

use MODX\CLI\Command\ProcessorCmd;
use Symfony\Component\Console\Input\InputArgument;

/**
 * A command to get detailed information about a MODX user
 */
class Get extends ProcessorCmd
{
    protected $processor = 'Security\\User\\Get';

    protected $name = 'user:get';
    protected $description = 'Get detailed information about a MODX user';

    protected function getArguments()
    {
        return array(
            array(
                'identifier',
                InputArgument::REQUIRED,
                'The user ID or username'
            ),
        );
    }

    protected function beforeRun(array &$properties = array(), array &$options = array())
    {
        $identifier = $this->argument('identifier');
        
        // If numeric, treat as ID; otherwise, treat as username
        if (is_numeric($identifier)) {
            $properties['id'] = (int)$identifier;
        } else {
            // For username lookup, we need to find the user first
            $user = $this->modx->getObject(\MODX\Revolution\modUser::class, ['username' => $identifier]);
            if (!$user) {
                $this->error("User not found: {$identifier}");
                return false;
            }
            $properties['id'] = $user->get('id');
        }
    }

    protected function processResponse(array $response = array())
    {
        if ($this->option('json')) {
            return parent::processResponse($response);
        }

        if (isset($response['success']) && $response['success']) {
            if (isset($response['object'])) {
                $user = $response['object'];
                $this->info('ID:          ' . ($user['id'] ?? ''));
                $this->info('Username:    ' . ($user['username'] ?? ''));
                $this->info('Email:       ' . ($user['email'] ?? ''));
                $this->info('Full Name:   ' . ($user['fullname'] ?? ''));
                $this->info('Active:      ' . ($user['active'] ? 'Yes' : 'No'));
                $this->info('Blocked:     ' . ($user['blocked'] ? 'Yes' : 'No'));
                $this->info('Sudo:        ' . (isset($user['sudo']) && $user['sudo'] ? 'Yes' : 'No'));
                if (isset($user['createdon']) && is_numeric($user['createdon'])) {
                    $this->info('Created:     ' . date('Y-m-d H:i:s', (int)$user['createdon']));
                }
                if (isset($user['lastlogin']) && is_numeric($user['lastlogin']) && $user['lastlogin'] > 0) {
                    $this->info('Last Login:  ' . date('Y-m-d H:i:s', (int)$user['lastlogin']));
                }
            }
            return 0;
        } else {
            $this->error('Failed to get user');
            if (isset($response['message'])) {
                $this->error($response['message']);
            }
            return 1;
        }
    }
}
