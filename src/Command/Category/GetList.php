<?php

namespace MODX\CLI\Command\Category;

use MODX\CLI\Command\ListProcessor;

/**
 * A command to get a list of categories in MODX
 */
class GetList extends ListProcessor
{
    protected $processor = 'Element\Category\GetList';
    protected $headers = array(
        'id', 'category', 'parent'
    );

    protected $name = 'category:list';
    protected $description = 'Get a list of categories in MODX';

    protected function parseValue($value, $column)
    {
        if ($column === 'parent') {
            return $this->renderObject('modCategory', $value, 'category');
        }

        return parent::parseValue($value, $column);
    }
}
