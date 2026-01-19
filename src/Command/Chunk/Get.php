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
    protected $required = ['id'];

    protected $name = 'chunk:get';
    protected $description = 'Get a MODX chunk';

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
                'The ID of the chunk to get'
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
     * Handle the processor response.
     *
     * @param array $response The processor response.
     * @return integer
     */
    protected function processResponse(array $response = [])
    {
        if (!isset($response['object'])) {
            if ($this->option('json') || $this->option('format') === 'json') {
                $this->output->writeln(json_encode([
                    'success' => false,
                    'message' => 'Chunk not found'
                ], JSON_PRETTY_PRINT));
                return 1;
            }
            $this->error('Chunk not found');
            return 1;
        }

        $chunk = $response['object'];

        // Check for both --json flag and --format=json
        if ($this->option('json') || $this->option('format') === 'json') {
            $this->output->writeln(json_encode($chunk, JSON_PRETTY_PRINT));
            return 0;
        }

        // Default to table format
        $table = new Table($this->output);
        $table->setHeaders(['Property', 'Value']);

        // Add basic properties
        $properties = [
            'id', 'name', 'description', 'category', 'locked', 'static', 'static_file', 'snippet'
        ];

        foreach ($properties as $property) {
            if (isset($chunk[$property])) {
                $value = $chunk[$property];

                // Format boolean values
                if ($property === 'locked' || $property === 'static') {
                    $value = $value ? 'Yes' : 'No';
                }

                // Format category
                if ($property === 'category' && !empty($value)) {
                    $category = $this->modx->getObject(\MODX\Revolution\modCategory::class, $value);
                    if ($category) {
                        $value .= ' (' . $category->get('category') . ')';
                    }
                }

                $table->addRow([$property, $value]);
            }
        }

        $table->render();
        return 0;
    }
}
