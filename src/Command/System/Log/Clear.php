<?php namespace MODX\CLI\Command\System\Log;

use MODX\CLI\Command\ProcessorCmd;

/**
 * A command to clear the MODX system log
 */
class Clear extends ProcessorCmd
{
    protected $processor = 'system/log/truncate';

    protected $name = 'system:log:clear';
    protected $description = 'Clear the MODX system log';

    protected function processResponse(array $response = array())
    {
        if (isset($response['success']) && $response['success']) {
            $this->info('System log cleared successfully');
        } else {
            $this->error('Failed to clear system log');
        }
    }
}
