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
    protected $required = array('provider');
    protected $headers = array(
        'id', 'name', 'description'
    );

    protected $name = 'package:provider:categories';
    protected $description = 'Get a list of categories from a provider in MODX';

    protected function getArguments()
    {
        return array(
            array(
                'provider',
                InputArgument::REQUIRED,
                'The ID of the provider'
            ),
        );
    }

    protected function beforeRun(array &$properties = array(), array &$options = array())
    {
        // Set node type to 'repository' to get categories
        // The GetNodes processor uses id format 'n_{type}_{key}'
        $properties['id'] = 'n_repository_0';
    }
}
