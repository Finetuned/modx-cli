<?php

namespace MODX\CLI\Command\Menu;

use MODX\CLI\Command\ListProcessor;

/**
 * A command to get a list of menus in MODX
 */
class GetList extends ListProcessor
{
    protected $processor = 'System\Menu\GetList';
    protected $headers = [
        'id', 'text', 'parent', 'action', 'namespace'
    ];

    protected $name = 'menu:list';
    protected $description = 'Get a list of menus in MODX';

    /**
     * Format raw values for output.
     *
     * @param mixed  $value  The raw column value.
     * @param string $column The column name.
     * @return mixed
     */
    protected function parseValue(mixed $value, string $column)
    {
        if ($column === 'parent') {
            return $this->renderObject('modMenu', $value, 'text');
        }

        if ($column === 'namespace') {
            return $this->renderObject('modNamespace', $value, 'name');
        }

        return parent::parseValue($value, $column);
    }
}
