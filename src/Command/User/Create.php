<?php

namespace MODX\CLI\Command\User;

use MODX\CLI\Command\ProcessorCmd;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to create a MODX user
 */
class Create extends ProcessorCmd
{
    protected $processor = 'Security\\User\\Create';

    protected $name = 'user:create';
    protected $description = 'Create a MODX user';

    protected function getArguments()
    {
        return array(
            array(
                'username',
                InputArgument::REQUIRED,
                'The username for the new user'
            ),
        );
    }

    protected function getOptions()
    {
        return array_merge(parent::getOptions(), array(
            array(
                'email',
                null,
                InputOption::VALUE_REQUIRED,
                'The email address for the user (required)'
            ),
            array(
                'password',
                null,
                InputOption::VALUE_REQUIRED,
                'The password for the user (will be generated if not provided)'
            ),
            array(
                'fullname',
                null,
                InputOption::VALUE_REQUIRED,
                'The full name of the user',
                ''
            ),
            array(
                'active',
                null,
                InputOption::VALUE_REQUIRED,
                'Active status (1 or 0)',
                '1'
            ),
            array(
                'blocked',
                null,
                InputOption::VALUE_REQUIRED,
                'Blocked status (1 or 0)',
                '0'
            ),
        ));
    }

    protected function beforeRun(array &$properties = array(), array &$options = array())
    {
        // Validate email is provided
        $email = $this->option('email');
        if (!$email) {
            $this->error('Email is required. Use --email=<address>');
            return false;
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Invalid email format');
            return false;
        }

        // Set username from argument
        $properties['username'] = $this->argument('username');
        $properties['email'] = $email;

        // Set password or generate one
        $password = $this->option('password');
        if (!$password) {
            $password = $this->generatePassword();
            $this->info('Generated password: ' . $password);
        }
        $properties['password'] = $password;
        $properties['passwordnotifymethod'] = 'none';

        // Set optional fields
        if ($this->option('fullname') !== null) {
            $properties['fullname'] = $this->option('fullname');
        }
        
        if ($this->option('active') !== null) {
            $properties['active'] = (int)$this->option('active');
        }
        
        if ($this->option('blocked') !== null) {
            $properties['blocked'] = (int)$this->option('blocked');
        }
    }

    protected function processResponse(array $response = array())
    {
        if ($this->option('json')) {
            return parent::processResponse($response);
        }
        
        if (isset($response['success']) && $response['success']) {
            $this->info('User created successfully');

            if (isset($response['object'])) {
                $user = $response['object'];
                if (isset($user['id'])) {
                    $this->info('User ID: ' . $user['id']);
                }
                if (isset($user['username'])) {
                    $this->info('Username: ' . $user['username']);
                }
            }
            return 0;
        } else {
            $this->error('Failed to create user');

            if (isset($response['message'])) {
                $this->error($response['message']);
            }
            return 1;
        }
    }

    /**
     * Generate a random password
     *
     * @param int $length
     * @return string
     */
    protected function generatePassword($length = 12)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_=+';
        $password = '';

        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[rand(0, strlen($chars) - 1)];
        }

        return $password;
    }
}
