<?php namespace MODX\CLI\Command\System;

use MODX\CLI\Command\ProcessorCmd;

/**
 * A command to clear the MODX cache
 */
class ClearCache extends ProcessorCmd
{
    protected $processor = 'system/clearcache';

    protected $name = 'system:clearcache';
    protected $description = 'Clear the MODX cache';

    protected function processResponse(array $response = array())
    {
        if (isset($response['success']) && $response['success']) {
            $this->info('Cache cleared successfully');
        } else {
            $this->error('Failed to clear cache');
        }
    }
}
