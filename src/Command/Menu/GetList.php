<?php namespace MODX\CLI\Command\Menu;

use MODX\CLI\Command\ListProcessor;

/**
 * A command to get a list of menus in MODX
 */
class GetList extends ListProcessor
{
    protected $processor = 'system/menu/getlist';
    protected $headers = array(
        'id', 'text', 'parent', 'action', 'namespace'
    );

    protected $name = 'menu:list';
    protected $description = 'Get a list of menus in MODX';

    protected function parseValue($value, $column)
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
