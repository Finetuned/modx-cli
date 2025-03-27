<?php namespace MODX\CLI\Command\System\Log\Actions;

use MODX\CLI\Command\ListProcessor;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to get a list of action logs in MODX
 */
class GetList extends ListProcessor
{
    protected $processor = 'system/log/getlist';
    protected $headers = array(
        'id', 'user', 'action', 'classKey', 'item', 'occurred'
    );

    protected $name = 'system:log:actions:list';
    protected $description = 'Get a list of action logs in MODX';

    protected function getOptions()
    {
        return array_merge(parent::getOptions(), array(
            array(
                'user',
                null,
                InputOption::VALUE_REQUIRED,
                'Filter by user ID'
            ),
            array(
                'action',
                null,
                InputOption::VALUE_REQUIRED,
                'Filter by action'
            ),
            array(
                'classKey',
                null,
                InputOption::VALUE_REQUIRED,
                'Filter by class key'
            ),
            array(
                'item',
                null,
                InputOption::VALUE_REQUIRED,
                'Filter by item'
            ),
            array(
                'dateStart',
                null,
                InputOption::VALUE_REQUIRED,
                'Filter by start date (YYYY-MM-DD)'
            ),
            array(
                'dateEnd',
                null,
                InputOption::VALUE_REQUIRED,
                'Filter by end date (YYYY-MM-DD)'
            ),
        ));
    }

    protected function beforeRun(array &$properties = array(), array &$options = array())
    {
        // Add filters based on options
        $optionKeys = array('user', 'action', 'classKey', 'item', 'dateStart', 'dateEnd');
        
        foreach ($optionKeys as $key) {
            if ($this->option($key) !== null) {
                $properties[$key] = $this->option($key);
            }
        }
    }

    protected function parseValue($value, $column)
    {
        if ($column === 'occurred') {
            return date('Y-m-d H:i:s', strtotime($value));
        }
        
        if ($column === 'user') {
            return $this->renderObject('modUser', $value, 'username');
        }
        
        return parent::parseValue($value, $column);
    }
}
