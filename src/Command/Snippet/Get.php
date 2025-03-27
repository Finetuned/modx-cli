<?php

namespace MODX\CLI\Command\Snippet;

use MODX\CLI\Command\ProcessorCmd;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to get a MODX snippet
 */
class Get extends ProcessorCmd
{
    protected $processor = 'element/snippet/get';
    protected $required = array('id');

    protected $name = 'snippet:get';
    protected $description = 'Get a MODX snippet';

    protected function getArguments()
    {
        return array(
            array(
                'id',
                InputArgument::REQUIRED,
                'The ID of the snippet to get'
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
                    'message' => 'Snippet not found'
                ], JSON_PRETTY_PRINT));
                return;
            }
            $this->error('Snippet not found');
            return;
        }

        $snippet = $response['object'];

        // Check for both --json flag and --format=json
        if ($this->option('json') || $this->option('format') === 'json') {
            $this->output->writeln(json_encode($snippet, JSON_PRETTY_PRINT));
            return;
        }

        // Default to table format
        $table = new Table($this->output);
        $table->setHeaders(array('Property', 'Value'));

        // Add basic properties
        $properties = array(
            'id', 'name', 'description', 'category', 'locked', 'static', 'static_file'
        );

        foreach ($properties as $property) {
            if (isset($snippet[$property])) {
                $value = $snippet[$property];

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

        // Display snippet code separately
        if (isset($snippet['snippet']) && !empty($snippet['snippet'])) {
            $this->output->writeln("\n<info>Snippet Code:</info>");
            $this->output->writeln($snippet['snippet']);
        }

        // Display properties if available
        if (isset($snippet['properties']) && !empty($snippet['properties'])) {
            $this->output->writeln("\n<info>Properties:</info>");
            if (is_string($snippet['properties'])) {
                $properties = json_decode($snippet['properties'], true);
                if ($properties) {
                    $this->output->writeln(json_encode($properties, JSON_PRETTY_PRINT));
                } else {
                    $this->output->writeln($snippet['properties']);
                }
            } else {
                $this->output->writeln(json_encode($snippet['properties'], JSON_PRETTY_PRINT));
            }
        }
    }
}
