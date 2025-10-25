<?php

namespace MODX\CLI\Command\Template;

use MODX\CLI\Command\ListProcessor;

/**
 * A command to get a list of templates in MODX
 */
class GetList extends ListProcessor
{
    protected $processor = 'Element\Template\GetList';
    protected $headers = array(
        'id', 'templatename', 'description', 'category'
    );

    protected $name = 'template:list';
    protected $description = 'Get a list of templates in MODX';

    protected function parseValue($value, $column)
    {
        if ($column === 'category') {
            return $this->renderObject('modCategory', $value, 'category');
        }

        return parent::parseValue($value, $column);
    }
}
