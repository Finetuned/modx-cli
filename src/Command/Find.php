<?php

namespace MODX\CLI\Command;

use Symfony\Component\Console\Input\InputArgument;

/**
 * Search (using the "uberbar" search)
 */
class Find extends ListProcessor
{
    protected $processor = 'Search\Search';
    protected $headers = [
        'name', 'type', 'description'
    ];

    protected $required = [
        'query'
    ];

    protected $name = 'find';
    protected $description = 'Search within this MODX instance using the "uberbar" search';

    /**
     * Prepare properties before running the processor.
     *
     * @param array $properties The processor properties.
     * @param array $options    The processor options.
     * @return boolean|null Return false to abort.
     */
    protected function beforeRun(array &$properties = [], array &$options = [])
    {
        $data = $this->modx->getVersionData();
        $version = $data['full_version'];
        if (version_compare($version, '2.3.0', '<')) {
            $this->error('This MODX version does not support that search function');
            return false;
        }
        return null;
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            [
                'query',
                InputArgument::REQUIRED,
                'The request to perform the search against'
            ],
        ];
    }
}
