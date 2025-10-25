<?php

namespace MODX\CLI\Command\TV;

use MODX\CLI\Command\ListProcessor;

/**
 * A command to get a list of template variables in MODX
 */
class GetList extends ListProcessor
{
    protected $processor = 'Element\Tv\GetList';
    protected $headers = array(
        'id', 'name', 'caption', 'description', 'category', 'type'
    );

    protected $name = 'tv:list';
    protected $description = 'Get a list of template variables in MODX';

    protected function parseValue($value, $column)
    {
        if ($column === 'category') {
            return $this->renderObject('modCategory', $value, 'category');
        }

        return parent::parseValue($value, $column);
    }
}
