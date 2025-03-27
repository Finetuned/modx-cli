<?php namespace MODX\CLI\Command\Package;

use MODX\CLI\Command\ListProcessor;

/**
 * A command to get a list of packages in MODX
 */
class GetList extends ListProcessor
{
    protected $processor = 'workspace/packages/getlist';
    protected $headers = array(
        'signature', 'name', 'version', 'release', 'installed', 'provider'
    );

    protected $name = 'package:list';
    protected $description = 'Get a list of packages in MODX';

    protected function parseValue($value, $column)
    {
        if ($column === 'installed') {
            return $value ? date('Y-m-d H:i:s', strtotime($value)) : 'Not installed';
        }
        
        if ($column === 'provider') {
            return $this->renderObject('transport.modTransportProvider', $value, 'name');
        }
        
        return parent::parseValue($value, $column);
    }
}
