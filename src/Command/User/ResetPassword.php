<?php

namespace MODX\CLI\Command\User;

use MODX\CLI\Command\ProcessorCmd;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to reset a user's password in MODX
 */
class ResetPassword extends ProcessorCmd
{
    protected $processor = 'security/user/update';
    protected $required = array('id');

    protected $name = 'user:resetpassword';
    protected $description = 'Reset a user\'s password in MODX';

    protected function getArguments()
    {
        return array(
            array(
                'id',
                InputArgument::REQUIRED,
                'The ID of the user'
            ),
        );
    }

    protected function getOptions()
    {
        return array_merge(parent::getOptions(), array(
            array(
                'password',
                null,
                InputOption::VALUE_REQUIRED,
                'The new password'
            ),
            array(
                'generate',
                'g',
                InputOption::VALUE_NONE,
                'Generate a random password'
            ),
        ));
    }

    protected function beforeRun(array &$properties = array(), array &$options = array())
    {
        $id = $this->argument('id');

        // Get the user to display information
        $user = $this->modx->getObject('modUser', $id);
        if (!$user) {
            $this->error("User with ID {$id} not found");
            return false;
        }

        $username = $user->get('username');

        // Generate a password if requested
        if ($this->option('generate')) {
            $password = $this->generatePassword();
        } else {
            $password = $this->option('password');
            if (!$password) {
                $password = $this->secret('Enter new password for user ' . $username . ':');
            }
        }

        // Add the password to the properties
        $properties['password'] = $password;
        $properties['passwordnotifymethod'] = 'none';

        // Store the password for later display
        $this->password = $password;
    }

    protected function processResponse(array $response = array())
    {
        if (isset($response['success']) && $response['success']) {
            $this->info('Password reset successfully');

            if (isset($this->password)) {
                $this->info('New password: ' . $this->password);
            }
        } else {
            $this->error('Failed to reset password');

            if (isset($response['message'])) {
                $this->error($response['message']);
            }
        }
    }

    /**
     * Generate a random password
     *
     * @param int $length
     *
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
