<?php

namespace MODX\CLI\Command\Package\Provider;

use MODX\CLI\Command\ListProcessor;

/**
 * A command to get a list of package providers in MODX
 */
class GetList extends ListProcessor
{
    protected $processor = 'workspace/providers/getlist';
    protected $headers = array(
        'id', 'name', 'service_url', 'username', 'verified'
    );

    protected $name = 'package:provider:list';
    protected $description = 'Get a list of package providers in MODX';

    protected function parseValue($value, $column)
    {
        if ($column === 'verified') {
            return $this->renderBoolean($value);
        }

        return parent::parseValue($value, $column);
    }
}
