<?php

namespace MODX\CLI\Command;

use Symfony\Component\Console\Input\InputArgument;

/**
 * Search (using the "uberbar" search)
 */
class Find extends ListProcessor
{
    protected $processor = 'Search\Search';
    protected $headers = array(
        'name', 'type', 'description'
    );

    protected $required = array(
        'query'
    );

    protected $name = 'find';
    protected $description = 'Search within this MODX instance using the "uberbar" search';

    protected function beforeRun(array &$properties = array(), array &$options = array())
    {
        $data = $this->modx->getVersionData();
        $version = $data['full_version'];
        if (version_compare($version, '2.3.0', '<')) {
            $this->error('This MODX version does not support that search function');
            return false;
        }
    }

    protected function getArguments()
    {
        return array(
            array(
                'query',
                InputArgument::REQUIRED,
                'The request to perform the search against'
            ),
        );
    }
}
