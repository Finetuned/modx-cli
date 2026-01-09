<?php

namespace MODX\CLI\Command\Context\Permissions;

use MODX\CLI\Command\ProcessorCmd;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to remove a context access permission
 */
class Remove extends ProcessorCmd
{
    protected $processor = 'Security\\Access\\UserGroup\\Context\\Remove';

    protected $name = 'context:permissions:remove';
    protected $description = 'Remove a context access permission';

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
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Force removal without confirmation'
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

        if (!$this->option('force')) {
            if (!$this->confirm('Are you sure you want to remove this access permission?')) {
                $this->info('Operation aborted');
                return false;
            }
        }
    }

    protected function processResponse(array $response = array())
    {
        if ($this->option('json')) {
            return parent::processResponse($response);
        }

        if (isset($response['success']) && $response['success']) {
            $this->info('Context access permission removed successfully');
            return 0;
        }

        $this->error('Failed to remove context access permission');
        if (isset($response['message'])) {
            $this->error($response['message']);
        }
        return 1;
    }
}
