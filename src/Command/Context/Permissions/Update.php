<?php

namespace MODX\CLI\Command\Context\Permissions;

use MODX\CLI\Command\ProcessorCmd;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to update a context access permission
 */
class Update extends ProcessorCmd
{
    protected $processor = 'Security\\Access\\UserGroup\\Context\\Update';

    protected $name = 'context:permissions:update';
    protected $description = 'Update a context access permission';

    protected function getArguments()
    {
        return array(
            array(
                'context',
                InputArgument::REQUIRED,
                'The context key'
            ),
            array(
                'id',
                InputArgument::REQUIRED,
                'The access control entry ID'
            ),
        );
    }

    protected function getOptions()
    {
        return array_merge(parent::getOptions(), array(
            array(
                'usergroup',
                null,
                InputOption::VALUE_REQUIRED,
                'The user group ID'
            ),
            array(
                'policy',
                null,
                InputOption::VALUE_REQUIRED,
                'The access policy ID'
            ),
            array(
                'authority',
                null,
                InputOption::VALUE_REQUIRED,
                'The authority level'
            ),
        ));
    }

    protected function beforeRun(array &$properties = array(), array &$options = array())
    {
        $context = $this->argument('context');
        $id = $this->argument('id');
        $properties['id'] = $id;

        $acl = $this->modx->getObject('MODX\\Revolution\\modAccessContext', $id);
        if (!$acl) {
            $this->error("Access control entry with ID {$id} not found");
            return false;
        }

        $target = $acl->get('target');
        if ($target !== $context) {
            $this->error("Access control entry {$id} is not for context '{$context}'");
            return false;
        }

        $properties['target'] = $context;
        $properties['principal'] = $this->option('usergroup') ?? $acl->get('principal');
        $properties['policy'] = $this->option('policy') ?? $acl->get('policy');

        if ($this->option('authority') !== null) {
            $properties['authority'] = (int) $this->option('authority');
        } else {
            $properties['authority'] = (int) $acl->get('authority');
        }
    }

    protected function processResponse(array $response = array())
    {
        if ($this->option('json')) {
            return parent::processResponse($response);
        }

        if (isset($response['success']) && $response['success']) {
            $this->info('Context access permission updated successfully');
            return 0;
        }

        $this->error('Failed to update context access permission');
        if (isset($response['message'])) {
            $this->error($response['message']);
        }
        return 1;
    }
}
