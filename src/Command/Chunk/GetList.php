<?php

namespace MODX\CLI\Command\Chunk;

use MODX\CLI\Command\ListProcessor;

/**
 * A command to get a list of chunks in MODX
 */
class GetList extends ListProcessor
{
    protected $processor = 'Element\Chunk\GetList';
    protected $headers = array(
        'id', 'name', 'description', 'category'
    );

    protected $name = 'chunk:list';
    protected $description = 'Get a list of chunks in MODX';

    protected function parseValue($value, $column)
    {
        if ($column === 'category') {
            return $this->renderObject('modCategory', $value, 'category');
        }

        return parent::parseValue($value, $column);
    }
}
