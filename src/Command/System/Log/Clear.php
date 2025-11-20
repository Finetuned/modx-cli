<?php

namespace MODX\CLI\Command\System\Log;

use MODX\CLI\Command\ProcessorCmd;

/**
 * A command to clear the MODX system log
 */
class Clear extends ProcessorCmd
{
    protected $processor = 'System\Log\Truncate';

    protected $name = 'system:log:clear';
    protected $description = 'Clear the MODX system log';

    protected function processResponse(array $response = array())
    {
        if ($this->option('json')) {
            return parent::processResponse($response);
        }

        if (isset($response['success']) && $response['success']) {
            $this->info('System log cleared successfully');
            return 0;
        } else {
            $this->error('Failed to clear system log');
            if (isset($response['message'])) {
                $this->error($response['message']);
            }
            return 1;
        }
    }
}
