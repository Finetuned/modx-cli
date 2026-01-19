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

    protected function getOptions()
    {
        return array_merge(parent::getOptions(), array(
            array(
                'username',
                null,
                InputOption::VALUE_REQUIRED,
                'The new username'
            ),
            array(
                'email',
                null,
                InputOption::VALUE_REQUIRED,
                'The new email address'
            ),
            array(
                'fullname',
                null,
                InputOption::VALUE_REQUIRED,
                'The full name'
            ),
            array(
                'active',
                null,
                InputOption::VALUE_REQUIRED,
                'Active status (1 or 0)'
            ),
            array(
                'blocked',
                null,
                InputOption::VALUE_REQUIRED,
                'Blocked status (1 or 0)'
            ),
        ));
    }

    protected function beforeRun(array &$properties = array(), array &$options = array())
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
        $optionKeys = array('username', 'email', 'fullname', 'active', 'blocked');

        foreach ($optionKeys as $key) {
            if ($this->option($key) !== null) {
                $properties[$key] = $this->option($key);
            }
        }
        
        // Ensure passwordnotifymethod is set to prevent unwanted notifications
        if (!isset($properties['passwordnotifymethod'])) {
            $properties['passwordnotifymethod'] = 'none';
        }
    }

    private function prePopulateFromObject(array &$properties, $object, string $class): void
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

    protected function processResponse(array $response = array())
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
