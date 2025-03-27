<?php namespace MODX\CLI\Command\Ns;

use MODX\CLI\Command\ListProcessor;

/**
 * A command to get a list of namespaces in MODX
 */
class GetList extends ListProcessor
{
    protected $processor = 'workspace/namespace/getlist';
    protected $headers = array(
        'id', 'name', 'path', 'assets_path'
    );

    protected $name = 'ns:list';
    protected $description = 'Get a list of namespaces in MODX';
}
