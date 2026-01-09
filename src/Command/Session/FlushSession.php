<?php

namespace MODX\CLI\Command\Session;

use MODX\CLI\Command\ProcessorCmd;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to flush all sessions in MODX
 */
class FlushSession extends ProcessorCmd
{
    protected $processor = 'Security\\Flush';

    protected $name = 'session:flush';
    protected $description = 'Flush all sessions in MODX';

    protected function getOptions()
    {
        return array_merge(parent::getOptions(), array(
            array(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Force flush without confirmation'
            ),
        ));
    }

    protected function beforeRun(array &$properties = array(), array &$options = array())
    {
        // Confirm flush unless --force is used
        if (!$this->option('force')) {
            if (!$this->confirm('Are you sure you want to flush all sessions? This will log out all users.')) {
                $this->info('Operation aborted');
                return false;
            }
        }

        if (!$this->ensureSessionHandler()) {
            $this->error('Session handler not available');
            return false;
        }
    }

    protected function processResponse(array $response = array())
    {
        if ($this->option('json')) {
            return parent::processResponse($response);
        }

        if (isset($response['success']) && $response['success']) {
            $this->info('Sessions flushed successfully');
            return 0;
        } else {
            $this->error('Failed to flush sessions');

            if (isset($response['message'])) {
                $this->error($response['message']);
            }
            return 1;
        }
    }

    private function ensureSessionHandler(): bool
    {
        if (!isset($this->modx->services) || !$this->modx->services) {
            return false;
        }

        if ($this->modx->services->has('session_handler')) {
            return true;
        }

        $handlerClass = $this->modx->getOption(
            'session_handler_class',
            null,
            'MODX\\Revolution\\modSessionHandler'
        );

        if (!is_string($handlerClass) || !class_exists($handlerClass)) {
            return false;
        }

        $handler = new $handlerClass($this->modx);
        if (!$handler instanceof \SessionHandlerInterface) {
            return false;
        }

        $this->modx->services->add('session_handler', $handler);
        session_set_save_handler($handler);

        return true;
    }
}
