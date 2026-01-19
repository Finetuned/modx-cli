<?php

namespace MODX\CLI\Command\User;

use MODX\CLI\Command\ProcessorCmd;
use MODX\CLI\Configuration\FieldMappings;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to update a MODX user
 */
class Update extends ProcessorCmd
{
    protected $processor = 'Security\\User\\Update';

    protected $name = 'user:update';
    protected $description = 'Update a MODX user';

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            [
                'identifier',
                InputArgument::REQUIRED,
                'The user ID or username'
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
                'username',
                null,
                InputOption::VALUE_REQUIRED,
                'The new username'
            ],
            [
                'email',
                null,
                InputOption::VALUE_REQUIRED,
                'The new email address'
            ],
            [
                'fullname',
                null,
                InputOption::VALUE_REQUIRED,
                'The full name'
            ],
            [
                'active',
                null,
                InputOption::VALUE_REQUIRED,
                'Active status (1 or 0)'
            ],
            [
                'blocked',
                null,
                InputOption::VALUE_REQUIRED,
                'Blocked status (1 or 0)'
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
        $identifier = $this->argument('identifier');
        $user = null;

        // If numeric, treat as ID; otherwise, treat as username and look up ID
        if (is_numeric($identifier)) {
            $properties['id'] = (int)$identifier;
            $user = $this->modx->getObject(\MODX\Revolution\modUser::class, $properties['id']);
            if (!$user) {
                $this->error("User not found: {$identifier}");
                return false;
            }
        } else {
            $user = $this->modx->getObject(\MODX\Revolution\modUser::class, ['username' => $identifier]);
            if (!$user) {
                $this->error("User not found: {$identifier}");
                return false;
            }
            $properties['id'] = $user->get('id');
        }

        // Pre-populate from existing user and profile
        $this->prePopulateFromObject($properties, $user, \MODX\Revolution\modUser::class);
        $profile = $user->getOne('Profile');
        if ($profile) {
            $this->prePopulateFromObject($properties, $profile, \MODX\Revolution\modUserProfile::class);
        }

        // Add options to the properties
        $optionKeys = ['username', 'email', 'fullname', 'active', 'blocked'];

        foreach ($optionKeys as $key) {
            if ($this->option($key) !== null) {
                $properties[$key] = $this->option($key);
            }
        }

        // Ensure passwordnotifymethod is set to prevent unwanted notifications
        if (!isset($properties['passwordnotifymethod'])) {
            $properties['passwordnotifymethod'] = 'none';
        }
        return null;
    }

    /**
     * Pre-populate properties from an existing object.
     *
     * @param array  $properties The processor properties.
     * @param mixed  $object     The MODX object.
     * @param string $class      The MODX class name.
     * @return void
     */
    private function prePopulateFromObject(array &$properties, mixed $object, string $class): void
    {
        if (!$object) {
            return;
        }

        $mapping = FieldMappings::get($class);
        foreach ($mapping as $propertyName => $fieldName) {
            if (!array_key_exists($propertyName, $properties) || $properties[$propertyName] === null) {
                $value = $object->get($fieldName);
                if ($value !== null && $value !== '') {
                    $properties[$propertyName] = $value;
                }
            }
        }
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
            $this->info('User updated successfully');

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
            $this->error('Failed to update user');

            if (isset($response['message'])) {
                $this->error($response['message']);
            }
            return 1;
        }
    }
}
