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

    protected $headers = [
        'id', 'usergroup', 'authority', 'policy_name'
    ];

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            [
                'context',
                \Symfony\Component\Console\Input\InputArgument::REQUIRED,
                'The context key'
            ],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array_merge(parent::getOptions(), [
            [
                'usergroup',
                null,
                \Symfony\Component\Console\Input\InputOption::VALUE_REQUIRED,
                'Filter by user group ID'
            ],
        ]);
    }

    /**
     * Prepare properties before running the processor.
     *
     * @param array $properties The processor properties.
     * @param array $options    The processor options.
     * @return boolean|null Return false to abort.
     */
    protected function beforeRun(array &$properties = [], array &$options = [])
    {
        $properties['type'] = 'MODX\\Revolution\\modAccessContext';
        $properties['target'] = $this->argument('context');

        if ($this->option('usergroup') !== null) {
            $properties['principal'] = $this->option('usergroup');
        }

        return parent::beforeRun($properties, $options);
    }

    /**
     * Handle the processor response.
     *
     * @param array $results The processor response.
     * @return integer
     */
    protected function processResponse(array $results = [])
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

    /**
     * Render the results as a table.
     *
     * @param array $results The list of results.
     * @return void
     */
    protected function renderBody(array $results = [])
    {
        $table = new \Symfony\Component\Console\Helper\Table($this->output);
        $table->setHeaders(['ID', 'User Group', 'Authority', 'Access Policy']);

        foreach ($results as $row) {
            $table->addRow($this->processRow($row));
        }

        $table->render();
    }
}
