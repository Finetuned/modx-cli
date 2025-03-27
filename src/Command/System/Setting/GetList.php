<?php

namespace MODX\CLI\Command\System\Setting;

use MODX\CLI\Command\ListProcessor;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to get a list of system settings in MODX
 */
class GetList extends ListProcessor
{
    protected $processor = 'system/settings/getlist';
    protected $headers = array(
        'key', 'value', 'name', 'description', 'area'
    );

    protected $name = 'system:setting:list';
    protected $description = 'Get a list of system settings in MODX';

    protected function getOptions()
    {
        return array_merge(parent::getOptions(), array(
            array(
                'area',
                null,
                InputOption::VALUE_REQUIRED,
                'Filter by area'
            ),
            array(
                'namespace',
                null,
                InputOption::VALUE_REQUIRED,
                'Filter by namespace'
            ),
            array(
                'query',
                null,
                InputOption::VALUE_REQUIRED,
                'Search query'
            ),
        ));
    }

    protected function beforeRun(array &$properties = array(), array &$options = array())
    {
        // Add filters based on options
        if ($this->option('area') !== null) {
            $properties['area'] = $this->option('area');
        }
        if ($this->option('namespace') !== null) {
            $properties['namespace'] = $this->option('namespace');
        }
        if ($this->option('query') !== null) {
            $properties['query'] = $this->option('query');
        }
    }

    protected function parseValue($value, $column)
    {
        if ($column === 'area') {
            return $this->renderObject('modNamespace', $value, 'name');
        }

        return parent::parseValue($value, $column);
    }
}
