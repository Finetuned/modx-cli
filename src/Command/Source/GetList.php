<?php

namespace MODX\CLI\Command\Source;

use MODX\CLI\Command\ListProcessor;

/**
 * A command to get a list of media sources in MODX
 */
class GetList extends ListProcessor
{
    protected $processor = 'Source\GetList';
    protected $headers = array(
        'id', 'name', 'description', 'class_key'
    );

    protected $name = 'source:list';
    protected $description = 'Get a list of media sources in MODX';

    protected function parseValue($value, $column)
    {
        if ($column === 'class_key') {
            // Extract the class name from the full class key
            $parts = explode('\\', $value);
            return end($parts);
        }

        return parent::parseValue($value, $column);
    }
}
