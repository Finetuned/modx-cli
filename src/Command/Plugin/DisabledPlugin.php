<?php

namespace MODX\CLI\Command\Plugin;

use MODX\CLI\Command\ListProcessor;

/**
 * A command to get a list of disabled plugins in MODX
 */
class DisabledPlugin extends ListProcessor
{
    protected $processor = 'element/plugin/getlist';
    protected $headers = array(
        'id', 'name', 'description', 'category'
    );

    protected $name = 'plugin:disabled';
    protected $description = 'Get a list of disabled plugins in MODX';
    protected $defaultsProperties = array(
        'disabled' => 1
    );

    protected function parseValue($value, $column)
    {
        if ($column === 'category') {
            return $this->renderObject('modCategory', $value, 'category');
        }

        return parent::parseValue($value, $column);
    }
}
