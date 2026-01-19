<?php

namespace MODX\CLI\Command\Package\Provider;

use MODX\CLI\Command\ListProcessor;
use Symfony\Component\Console\Input\InputArgument;

/**
 * A command to get a list of categories from a provider in MODX
 */
class CategoriesList extends ListProcessor
{
    protected $processor = 'Workspace\Packages\Rest\GetNodes';
    protected $required = ['provider'];
    protected $headers = [
        'id', 'name', 'description'
    ];

    protected $name = 'package:provider:categories';
    protected $description = 'Get a list of categories from a provider in MODX';

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            [
                'provider',
                InputArgument::REQUIRED,
                'The ID of the provider'
            ],
        ];
    }

    /**
     * Prepare properties before running the processor.
     *
     * @param array $properties The processor properties.
     * @param array $options    The processor options.
     * @return void
     */
    protected function beforeRun(array &$properties = [], array &$options = [])
    {
        // Set node type to 'repository' to get categories
        // The GetNodes processor uses id format 'n_{type}_{key}'
        $properties['id'] = 'n_repository_0';
    }
}
