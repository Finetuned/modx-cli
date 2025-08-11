<?php

namespace MODX\CLI\Command;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputOption;

/**
 * Command to deal with list processors (ie. list)
 */
abstract class ListProcessor extends ProcessorCmd
{
    protected $headers = array(
        'id', 'name', 'description'
    );
    protected $showPagination = true;

    protected function init()
    {
        $success = parent::init();
        if ($success && $this->modx) {
            $version = $this->modx->getVersionData();
            if ($version && isset($version['full_version']) && !version_compare($version['full_version'], '3.0.0-pl', '>=')) {
                // Add a default limit to processors do not list everything
                $this->defaultsProperties['limit'] = 10;
            }
        }

        return $success;
    }

    protected function getOptions()
    {
        $parentOptions = parent::getOptions();
        $paginationOptions = array();
        
        // Check if limit option already exists in parent options
        $hasLimit = false;
        $hasStart = false;
        
        foreach ($parentOptions as $option) {
            if (isset($option[0]) && $option[0] === 'limit') {
                $hasLimit = true;
            }
            if (isset($option[0]) && $option[0] === 'start') {
                $hasStart = true;
            }
        }
        
        // Add pagination options only if they don't already exist
        if (!$hasLimit) {
            $paginationOptions[] = array(
                'limit',
                'l',
                InputOption::VALUE_REQUIRED,
                'Number of items to return (default: 10)',
                10
            );
        }
        
        if (!$hasStart) {
            $paginationOptions[] = array(
                'start',
                null,
                InputOption::VALUE_REQUIRED,
                'Starting index for pagination (default: 0)',
                0
            );
        }
        
        return array_merge($parentOptions, $paginationOptions);
    }

    protected function beforeRun(array &$properties = array(), array &$options = array())
    {
        // Add pagination options to properties
        if ($this->option('limit') !== null) {
            $properties['limit'] = (int) $this->option('limit');
        }
        
        if ($this->option('start') !== null) {
            $properties['start'] = (int) $this->option('start');
        }
        
        return parent::beforeRun($properties, $options);
    }
    protected function processResponse(array $results = array())
    {
        $total = $results['total'];
        $results = $results['results'];

        if ($this->option('json')) {
            $output = [
                'total' => $total,
                'results' => $results
            ];
            $this->output->writeln(json_encode($output, JSON_PRETTY_PRINT));
            return 0;
        }

        $this->renderBody($results);
        if ($this->showPagination) {
            $this->renderPagination($results, $total);
        }

        return 0; // Return 0 for success
    }

    /**
     * Render the results as a table
     *
     * @param array $results
     */
    protected function renderBody(array $results = array())
    {
        /** @var \Symfony\Component\Console\Helper\Table $table */
        $table = new Table($this->output);
        $table->setHeaders($this->headers);

        foreach ($results as $row) {
            $table->addRow($this->processRow($row));
        }

        $table->render();
    }

    /**
     * Render the "pagination" table
     *
     * @param array $results
     * @param int $total
     *
     * @return void
     */
    protected function renderPagination(array $results = array(), $total = 0)
    {
        /** @var \Symfony\Component\Console\Helper\Table $t */
        $table = new Table($this->output);
        $table->setHeaders(array('', ''));
        $table->setStyle('compact');

        $table->setRows(array(
            array(
                'displaying ' . count($results) . ' item(s)',
                'of ' . $total,
            ),
            array('',''),
        ));

        $table->render();
    }
}
