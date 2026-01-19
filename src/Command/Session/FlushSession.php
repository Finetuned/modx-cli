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
                'Force flush without confirmation'
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
            if (!isset($response['message']) || $response['message'] === '') {
                if (isset($response['success']) && $response['success']) {
                    $response['message'] = 'Sessions flushed successfully';
                } else {
                    $response['message'] = 'Failed to flush sessions';
                }
            }

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

    /**
     * Ensure the session handler service exists.
     *
     * @return boolean
     */
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
