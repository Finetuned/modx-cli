<?php namespace MODX\CLI\Command\User;

use MODX\CLI\Command\ListProcessor;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to get a list of users in MODX
 */
class GetList extends ListProcessor
{
    protected $processor = 'security/user/getlist';
    protected $headers = array(
        'id', 'username', 'fullname', 'email', 'active'
    );

    protected $name = 'user:list';
    protected $description = 'Get a list of users in MODX';

    protected function getOptions()
    {
        return array_merge(parent::getOptions(), array(
            array(
                'active',
                null,
                InputOption::VALUE_REQUIRED,
                'Filter by active status (1 or 0)'
            ),
            array(
                'blocked',
                null,
                InputOption::VALUE_REQUIRED,
                'Filter by blocked status (1 or 0)'
            ),
            array(
                'usergroup',
                null,
                InputOption::VALUE_REQUIRED,
                'Filter by user group ID'
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
        if ($this->option('active') !== null) {
            $properties['active'] = $this->option('active');
        }
        if ($this->option('blocked') !== null) {
            $properties['blocked'] = $this->option('blocked');
        }
        if ($this->option('usergroup') !== null) {
            $properties['usergroup'] = $this->option('usergroup');
        }
        if ($this->option('query') !== null) {
            $properties['query'] = $this->option('query');
        }
    }

    protected function parseValue($value, $column)
    {
        if ($column === 'active') {
            return $this->renderBoolean($value);
        }
        
        return parent::parseValue($value, $column);
    }
}
