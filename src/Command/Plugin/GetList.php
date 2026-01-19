<?php

namespace MODX\CLI\Command\Plugin;

use MODX\CLI\Command\ListProcessor;

/**
 * A command to get a list of plugins in MODX
 */
class GetList extends ListProcessor
{
    protected $processor = 'Element\Plugin\GetList';
    protected $headers = [
        'id', 'name', 'description', 'category', 'disabled'
    ];

    protected $name = 'plugin:list';
    protected $description = 'Get a list of plugins in MODX';

    /**
     * Format raw values for output.
     *
     * @param mixed  $value  The raw column value.
     * @param string $column The column name.
     * @return mixed
     */
    protected function parseValue(mixed $value, string $column)
    {
        if ($column === 'category') {
            return $this->renderObject('modCategory', $value, 'category');
        }

        if ($column === 'disabled') {
            return $this->renderBoolean($value);
        }

        return parent::parseValue($value, $column);
    }
}
