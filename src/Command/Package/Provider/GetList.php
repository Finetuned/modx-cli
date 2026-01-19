<?php

namespace MODX\CLI\Command\Package\Provider;

use MODX\CLI\Command\ListProcessor;

/**
 * A command to get a list of package providers in MODX
 */
class GetList extends ListProcessor
{
    protected $processor = 'Workspace\Providers\GetList';
    protected $headers = [
        'id', 'name', 'service_url', 'username', 'verified'
    ];

    protected $name = 'package:provider:list';
    protected $description = 'Get a list of package providers in MODX';

    /**
     * Format raw values for output.
     *
     * @param mixed  $value  The raw column value.
     * @param string $column The column name.
     * @return mixed
     */
    protected function parseValue(mixed $value, string $column)
    {
        if ($column === 'verified') {
            return $this->renderBoolean($value);
        }

        return parent::parseValue($value, $column);
    }
}
