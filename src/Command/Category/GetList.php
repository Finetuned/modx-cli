<?php

namespace MODX\CLI\Command\Category;

use MODX\CLI\Command\ListProcessor;

/**
 * A command to get a list of categories in MODX
 */
class GetList extends ListProcessor
{
    protected $processor = 'Element\Category\GetList';
    protected $headers = [
        'id', 'category', 'parent'
    ];

    protected $name = 'category:list';
    protected $description = 'Get a list of categories in MODX';

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
            return $this->renderObject('modCategory', $value, 'category');
        }

        return parent::parseValue($value, $column);
    }
}
