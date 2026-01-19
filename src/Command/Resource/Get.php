<?php

namespace MODX\CLI\Command\Resource;

use MODX\CLI\Command\ProcessorCmd;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to get a MODX resource
 */
class Get extends ProcessorCmd
{
    protected $processor = 'Resource\Get';
    protected $required = ['id'];

    protected $name = 'resource:get';
    protected $description = 'Get a MODX resource';

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            [
                'id',
                InputArgument::REQUIRED,
                'The ID of the resource to get'
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
                'format',
                'f',
                InputOption::VALUE_REQUIRED,
                'Output format (table, json)',
                'table'
            ],
        ]);
    }

    /**
     * Process processor response.
     *
     * @param array $response The decoded processor response.
     * @return integer
     */
    protected function processResponse(array $response = [])
    {
        if (!isset($response['object'])) {
            if ($this->option('json') || $this->option('format') === 'json') {
                $this->output->writeln(json_encode([
                    'success' => false,
                    'message' => 'Resource not found'
                ], JSON_PRETTY_PRINT));
                return 1;
            }
            $this->error('Resource not found');
            return 1;
        }

        $resource = $response['object'];

        // Check for both --json flag and --format=json
        if ($this->option('json') || $this->option('format') === 'json') {
            $this->output->writeln(json_encode($resource, JSON_PRETTY_PRINT));
            return 0;
        }

        // Default to table format
        $table = new Table($this->output);
        $table->setHeaders(['Property', 'Value']);

        // Add basic properties
        $properties = [
            'id', 'pagetitle', 'longtitle', 'description', 'alias', 'published',
            'hidemenu', 'parent', 'template', 'menuindex', 'searchable', 'cacheable',
            'createdby', 'createdon', 'editedby', 'editedon', 'publishedon', 'publishedby',
            'context_key', 'content'
        ];

        foreach ($properties as $property) {
            if (isset($resource[$property])) {
                $value = $resource[$property];

                // Format boolean values
                if (
                    $property === 'published' || $property === 'hidemenu' ||
                    $property === 'searchable' || $property === 'cacheable'
                ) {
                    $value = $value ? 'Yes' : 'No';
                }

                // Format dates
                if ($property === 'createdon' || $property === 'editedon' || $property === 'publishedon') {
                    if (!empty($value)) {
                        $value = date('Y-m-d H:i:s', strtotime($value));
                    }
                }

                $table->addRow([$property, $value]);
            }
        }

        $table->render();
        return 0;
    }
}
