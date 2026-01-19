<?php

namespace MODX\CLI\Command\Package;

use MODX\CLI\Command\ListProcessor;

/**
 * A command to get a list of packages in MODX
 */
class GetList extends ListProcessor
{
    protected $processor = 'Workspace\Packages\GetList';
    protected $headers = [
        'signature', 'name', 'version', 'release', 'installed', 'provider'
    ];

    protected $name = 'package:list';
    protected $description = 'Get a list of packages in MODX';

    /**
     * Parse column values for display.
     *
     * @param mixed  $value  The column value.
     * @param string $column The column name.
     * @return mixed
     */
    protected function parseValue(mixed $value, string $column)
    {
        if ($column === 'installed') {
            return $value ? date('Y-m-d H:i:s', strtotime($value)) : 'Not installed';
        }

        if ($column === 'provider') {
            return $this->renderObject('transport.modTransportProvider', $value, 'name');
        }

        return parent::parseValue($value, $column);
    }
}
