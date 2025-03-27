<?php

namespace MODX\CLI\Command\System;

use MODX\CLI\Command\ProcessorCmd;

/**
 * A command to refresh URIs in MODX
 */
class RefreshURIs extends ProcessorCmd
{
    protected $processor = 'system/refreshuris';

    protected $name = 'system:refreshuris';
    protected $description = 'Refresh URIs in MODX';

    protected function processResponse(array $response = array())
    {
        if (isset($response['success']) && $response['success']) {
            $this->info('URIs refreshed successfully');

            if (isset($response['total'])) {
                $this->info('Total resources processed: ' . $response['total']);
            }
            return 0; // Return 0 for success
        } else {
            $this->error('Failed to refresh URIs');
            return 1; // Return non-zero for failure
        }
    }
}
