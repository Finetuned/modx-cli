<?php

namespace MODX\CLI\Command\Context\Permissions;

use MODX\CLI\Command\ListProcessor;

/**
 * A command to list context access permissions for a user group
 */
class GetList extends ListProcessor
{
    protected $processor = 'Security\\Access\\GetList';

    protected $name = 'context:permissions';
    protected $description = 'List context access permissions for a context';

    protected $headers = array(
        'id', 'usergroup', 'authority', 'policy_name'
    );

    protected function getArguments()
    {
        return array(
            array(
                'context',
                \Symfony\Component\Console\Input\InputArgument::REQUIRED,
                'The context key'
            ),
        );
    }

    protected function getOptions()
    {
        return array_merge(parent::getOptions(), array(
            array(
                'usergroup',
                null,
                \Symfony\Component\Console\Input\InputOption::VALUE_REQUIRED,
                'Filter by user group ID'
            ),
        ));
    }

    protected function beforeRun(array &$properties = array(), array &$options = array())
    {
        $properties['type'] = 'MODX\\Revolution\\modAccessContext';
        $properties['target'] = $this->argument('context');

        if ($this->option('usergroup') !== null) {
            $properties['principal'] = $this->option('usergroup');
        }

        return parent::beforeRun($properties, $options);
    }

    protected function processResponse(array $results = array())
    {
        if (isset($results['results']) && is_array($results['results'])) {
            foreach ($results['results'] as &$row) {
                if (isset($row['principal_name']) && !isset($row['usergroup'])) {
                    $row['usergroup'] = $row['principal_name'];
                }
            }
            unset($row);
        }

        return parent::processResponse($results);
    }

    protected function renderBody(array $results = array())
    {
        $table = new \Symfony\Component\Console\Helper\Table($this->output);
        $table->setHeaders(array('ID', 'User Group', 'Authority', 'Access Policy'));

        foreach ($results as $row) {
            $table->addRow($this->processRow($row));
        }

        $table->render();
    }
}
