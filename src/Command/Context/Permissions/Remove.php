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

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            [
                'context',
                InputArgument::REQUIRED,
                'The context key'
            ],
            [
                'id',
                InputArgument::REQUIRED,
                'The access control entry ID'
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
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Force removal without confirmation'
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
