<?php

namespace MODX\CLI\Command\Plugin;

use MODX\CLI\Command\ListProcessor;

/**
 * A command to get a list of plugins in MODX
 */
class GetList extends ListProcessor
{
    protected $processor = 'element/plugin/getlist';
    protected $headers = array(
        'id', 'name', 'description', 'category', 'disabled'
    );

    protected $name = 'plugin:list';
    protected $description = 'Get a list of plugins in MODX';

    protected function parseValue($value, $column)
    {
        if ($column === 'category') {
            return $this->renderObject('modCategory', $value, 'category');
        }

        if ($column === 'disabled') {
            return $this->renderBoolean(!$value);
        }

        return parent::parseValue($value, $column);
    }
}
