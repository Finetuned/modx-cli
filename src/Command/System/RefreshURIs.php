<?php

namespace MODX\CLI\Command\System;

use MODX\CLI\Command\ProcessorCmd;

/**
 * A command to refresh URIs in MODX
 */
class RefreshURIs extends ProcessorCmd
{
    protected $processor = 'System\RefreshUris';

    protected $name = 'system:refreshuris';
    protected $description = 'Refresh URIs in MODX';

    /**
     * Handle the processor response.
     *
     * @param array $response The processor response.
     * @return integer
     */
    protected function processResponse(array $response = [])
    {
        if (isset($response['success']) && $response['success']) {
            $this->info($this->trans('system.refresh_uris.success', [], 'commands'));

            if (isset($response['total'])) {
                $this->info($this->trans('system.refresh_uris.count_label', [], 'commands') . $response['total']);
            }
            return 0; // Return 0 for success
        } else {
            $this->error($this->trans('system.refresh_uris.failed', [], 'commands'));
            return 1; // Return non-zero for failure
        }
    }
}
