<?php

namespace MODX\CLI\Command;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputOption;

/**
 * Command to deal with list processors (ie. list)
 */
abstract class ListProcessor extends ProcessorCmd
{
    protected $headers = [
        'id', 'name', 'description'
    ];
    protected $showPagination = true;

    /**
     * Initialize the command.
     *
     * @return boolean
     */
    protected function init()
    {
        $success = parent::init();
        if ($success && $this->modx) {
            $version = $this->modx->getVersionData();
            if (
                $version &&
                isset($version['full_version']) &&
                !version_compare($version['full_version'], '3.0.0-pl', '>=')
            ) {
                // Add a default limit to processors do not list everything
                $this->defaultsProperties['limit'] = 10;
            }
        }

        return $success;
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        $parentOptions = parent::getOptions();
        $paginationOptions = [];

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
            $paginationOptions[] = [
                'limit',
                'l',
                InputOption::VALUE_REQUIRED,
                'Number of items to return (default: 10)',
                10
            ];
        }

        if (!$hasStart) {
            $paginationOptions[] = [
                'start',
                null,
                InputOption::VALUE_REQUIRED,
                'Starting index for pagination (default: 0)',
                0
            ];
        }

        return array_merge($parentOptions, $paginationOptions);
    }

    /**
     * Prepare properties before running the processor.
     *
     * @param array $properties The processor properties.
     * @param array $options    The processor options.
     * @return mixed
     */
    protected function beforeRun(array &$properties = [], array &$options = [])
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

    /**
     * Handle the processor response.
     *
     * @param array $results The processor response.
     * @return integer
     */
    protected function processResponse(array $results = [])
    {
        // Some MODX list processors omit a separate total in unit tests; fall back to count.
        $items = $results['results'] ?? [];
        $total = $results['total'] ?? count($items);

        if ($this->option('json')) {
            $output = [
                'total' => $total,
                'results' => $items
            ];
            $this->output->writeln(json_encode($output, JSON_PRETTY_PRINT));
            return 0;
        }

        $this->renderBody($items);
        if ($this->showPagination) {
            $this->renderPagination($items, $total);
        }

        return 0; // Return 0 for success
    }

    /**
     * Render the results as a table.
     *
     * @param array $results The list of results.
     * @return void
     */
    protected function renderBody(array $results = [])
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
     * Render the "pagination" table.
     *
     * @param array   $results The list of results.
     * @param integer $total   The total count.
     *
     * @return void
     */
    protected function renderPagination(array $results = [], int $total = 0)
    {
        /** @var \Symfony\Component\Console\Helper\Table $table */
        $table = new Table($this->output);
        $table->setHeaders(['', '']);
        $table->setStyle('compact');

        $table->setRows([
            [
                'displaying ' . count($results) . ' item(s)',
                'of ' . $total,
            ],
            ['',''],
        ]);

        $table->render();
    }
}
