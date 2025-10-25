<?php

namespace MODX\CLI\Command\Chunk;

use MODX\CLI\Command\ProcessorCmd;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to get a MODX chunk
 */
class Get extends ProcessorCmd
{
    protected $processor = 'Element\Chunk\Get';
    protected $required = array('id');

    protected $name = 'chunk:get';
    protected $description = 'Get a MODX chunk';

    protected function getArguments()
    {
        return array(
            array(
                'id',
                InputArgument::REQUIRED,
                'The ID of the chunk to get'
            ),
        );
    }

    protected function getOptions()
    {
        return array_merge(parent::getOptions(), array(
            array(
                'format',
                'f',
                InputOption::VALUE_REQUIRED,
                'Output format (table, json)',
                'table'
            ),
        ));
    }

    protected function processResponse(array $response = array())
    {
        if (!isset($response['object'])) {
            if ($this->option('json') || $this->option('format') === 'json') {
                $this->output->writeln(json_encode([
                    'success' => false,
                    'message' => 'Chunk not found'
                ], JSON_PRETTY_PRINT));
                return;
            }
            $this->error('Chunk not found');
            return;
        }

        $chunk = $response['object'];

        // Check for both --json flag and --format=json
        if ($this->option('json') || $this->option('format') === 'json') {
            $this->output->writeln(json_encode($chunk, JSON_PRETTY_PRINT));
            return;
        }

        // Default to table format
        $table = new Table($this->output);
        $table->setHeaders(array('Property', 'Value'));

        // Add basic properties
        $properties = array(
            'id', 'name', 'description', 'category', 'locked', 'static', 'static_file', 'snippet'
        );

        foreach ($properties as $property) {
            if (isset($chunk[$property])) {
                $value = $chunk[$property];

                // Format boolean values
                if ($property === 'locked' || $property === 'static') {
                    $value = $value ? 'Yes' : 'No';
                }

                // Format category
                if ($property === 'category' && !empty($value)) {
                    $category = $this->modx->getObject('modCategory', $value);
                    if ($category) {
                        $value .= ' (' . $category->get('category') . ')';
                    }
                }

                $table->addRow(array($property, $value));
            }
        }

        $table->render();
    }
}
