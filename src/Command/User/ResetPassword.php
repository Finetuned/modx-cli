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
    protected $processor = 'Security\User\Update';
    protected $required = ['id'];

    protected $name = 'user:resetpassword';
    protected $description = 'Reset a user\'s password in MODX';
    /**
     * @var string|null
     */
    protected $password;

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
                'The ID of the user'
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
                'password',
                null,
                InputOption::VALUE_REQUIRED,
                'The new password'
            ],
            [
                'generate',
                'g',
                InputOption::VALUE_NONE,
                'Generate a random password'
            ],
        ]);
    }

    /**
     * Prepare processor properties before execution.
     *
     * @param array $properties The processor properties.
     * @param array $options    The processor options.
     * @return boolean|null False to abort execution, otherwise null.
     */
    protected function beforeRun(array &$properties = [], array &$options = [])
    {
        $id = $this->argument('id');

        // Get the user to display information
        $user = $this->modx->getObject(\MODX\Revolution\modUser::class, $id);
        if (!$user) {
            $this->error($this->trans('user.resetpassword.not_found', ['%id%' => $id], 'commands'));
            return false;
        }

        $username = $user->get('username');
        $properties['username'] = $username;

        $profile = $user->getOne('Profile');
        if ($profile) {
            $properties['email'] = $profile->get('email');
        }

        // Generate a password if requested
        if ($this->option('generate')) {
            $password = $this->generatePassword();
        } else {
            $password = $this->option('password');
            if (!$password) {
                $password = $this->secret($this->trans('user.resetpassword.prompt', ['%username%' => $username], 'commands'));
            }
        }

        // Add the password to the properties
        $properties['password'] = $password;
        $properties['passwordnotifymethod'] = 'none';

        // Store the password for later display
        $this->password = $password;
        return null;
    }

    /**
     * Process processor response.
     *
     * @param array $response The decoded processor response.
     * @return integer
     */
    protected function processResponse(array $response = [])
    {
        if ($this->option('json')) {
            return parent::processResponse($response);
        }

        if (isset($response['success']) && $response['success']) {
            $this->info($this->trans('user.resetpassword.success', [], 'commands'));

            if (isset($this->password)) {
                $this->info($this->trans('user.resetpassword.new_password_label', [], 'commands') . $this->password);
            }
            return 0;
        } else {
            $this->error($this->trans('user.resetpassword.failed', [], 'commands'));

            if (isset($response['message'])) {
                $this->error($response['message']);
            }
            return 1;
        }
    }

    /**
     * Generate a random password.
     *
     * @param integer $length The password length.
     * @return string
     */
    protected function generatePassword(int $length = 12)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_=+';
        $password = '';

        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[rand(0, strlen($chars) - 1)];
        }

        return $password;
    }
}
