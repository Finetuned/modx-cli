<?php

namespace MODX\CLI\Command\Package\Provider;

use MODX\CLI\Command\ListProcessor;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to get a list of packages from a provider in MODX
 */
class PackagesList extends ListProcessor
{
    protected $processor = 'Workspace\Packages\Rest\GetList';
    protected $required = array('provider');
    protected $headers = array(
        'signature', 'name', 'version', 'release', 'installed'
    );

    protected $name = 'package:provider:packages';
    protected $description = 'Get a list of packages from a provider in MODX';

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

    protected function getOptions()
    {
        return array_merge(parent::getOptions(), array(
            array(
                'query',
                null,
                InputOption::VALUE_REQUIRED,
                'Search query'
            ),
            array(
                'category',
                null,
                InputOption::VALUE_REQUIRED,
                'Filter by category'
            ),
        ));
    }

    protected function beforeRun(array &$properties = array(), array &$options = array())
    {
        // Add filters based on options
        if ($this->option('query') !== null) {
            $properties['query'] = $this->option('query');
        }
        if ($this->option('category') !== null) {
            $properties['category'] = $this->option('category');
        }
    }

    protected function parseValue($value, $column)
    {
        if ($column === 'installed') {
            return $value ? 'Yes' : 'No';
        }

        return parent::parseValue($value, $column);
    }
}
