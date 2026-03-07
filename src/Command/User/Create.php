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

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            [
                'username',
                InputArgument::REQUIRED,
                'The username for the new user'
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
                'email',
                null,
                InputOption::VALUE_REQUIRED,
                'The email address for the user (required)'
            ],
            [
                'password',
                null,
                InputOption::VALUE_REQUIRED,
                'The password for the user (will be generated if not provided)'
            ],
            [
                'fullname',
                null,
                InputOption::VALUE_REQUIRED,
                'The full name of the user',
                ''
            ],
            [
                'active',
                null,
                InputOption::VALUE_REQUIRED,
                'Active status (1 or 0)',
                '1'
            ],
            [
                'blocked',
                null,
                InputOption::VALUE_REQUIRED,
                'Blocked status (1 or 0)',
                '0'
            ],
        ]);
    }

    /**
     * Prepare properties before running the processor.
     *
     * @param array $properties The processor properties.
     * @param array $options    The processor options.
     * @return boolean|null Return false to abort.
     */
    protected function beforeRun(array &$properties = [], array &$options = [])
    {
        // Validate email is provided
        $email = $this->option('email');
        if (!$email) {
            $this->error($this->trans('user.create.email_required', [], 'commands'));
            return false;
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error($this->trans('user.create.invalid_email', [], 'commands'));
            return false;
        }

        // Set username from argument
        $properties['username'] = $this->argument('username');
        $properties['email'] = $email;

        // Set password or generate one
        $password = $this->option('password');
        if (!$password) {
            $password = $this->generatePassword();
            $this->info($this->trans('user.create.generated_password', ['%password%' => $password], 'commands'));
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
        return null;
    }

    /**
     * Handle the processor response.
     *
     * @param array $response The processor response.
     * @return integer
     */
    protected function processResponse(array $response = [])
    {
        if ($this->option('json')) {
            return parent::processResponse($response);
        }

        if (isset($response['success']) && $response['success']) {
            $this->info($this->trans('user.create.success', [], 'commands'));

            if (isset($response['object'])) {
                $user = $response['object'];
                if (isset($user['id'])) {
                    $this->info($this->trans('user.create.user_id_label', [], 'commands') . $user['id']);
                }
                if (isset($user['username'])) {
                    $this->info($this->trans('user.create.username_label', [], 'commands') . $user['username']);
                }
            }
            return 0;
        } else {
            $this->error($this->trans('user.create.failed', [], 'commands'));

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
