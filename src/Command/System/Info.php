<?php

namespace MODX\CLI\Command\System;

use MODX\CLI\Command\ListProcessor;

/**
 * A command to get general system information
 */
class Info extends ListProcessor
{
    protected $processor = 'System\Info';
    protected $headers = [
        'modx_version', 'database_name', 'table_prefix', 'servertime', 'database_type', 'database_version'
    ];

    protected $name = 'system:info';
    protected $description = 'Get general system information';
    protected $showPagination = false;

    /**
     * Decode the processor response and normalize to a list format.
     *
     * @param \MODX\Revolution\Processors\ProcessorResponse $response The processor response.
     * @return array|mixed
     */
    protected function decodeResponse(\MODX\Revolution\Processors\ProcessorResponse &$response)
    {
        $data = parent::decodeResponse($response);
        $data['results'] = [$data['object']];
        unset($data['object']);

        return $data;
    }
}
