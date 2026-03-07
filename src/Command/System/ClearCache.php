<?php

namespace MODX\CLI\Command\System;

use MODX\CLI\Command\ProcessorCmd;

/**
 * A command to clear the MODX cache
 */
class ClearCache extends ProcessorCmd
{
    protected $processor = 'System\ClearCache';

    protected $name = 'system:clearcache';
    protected $description = 'Clear the MODX cache';

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
            $this->info($this->trans('system.cache.clear.success', [], 'commands'));
            return 0;
        } else {
            $this->error($this->trans('system.cache.clear.failed', [], 'commands'));
            if (isset($response['message'])) {
                $this->error($response['message']);
            }
            return 1;
        }
    }
}
