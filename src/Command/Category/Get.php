<?php

namespace MODX\CLI\Command\Category;

use MODX\CLI\Command\ProcessorCmd;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to get a MODX category
 */
class Get extends ProcessorCmd
{
    protected $processor = 'Element\Category\Get';
    protected $required = ['id'];

    protected $name = 'category:get';
    protected $description = 'Get a MODX category';

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
                'The ID of the category to get'
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
                $payload = ['success' => false, 'message' => 'Category not found'];
                $this->output->writeln(json_encode($payload, JSON_PRETTY_PRINT));
                return 0;
            }
            $this->output->writeln('<error>Category not found</error>');
            return 1;
        }

        $category = $response['object'];

        // Check for both --json flag and --format=json
        if ($this->option('json') || $this->option('format') === 'json') {
            $this->output->writeln(json_encode($category, JSON_PRETTY_PRINT));
            return 0;
        }

        // Default to table format
        $table = new Table($this->output);
        $table->setHeaders(['Property', 'Value']);

        // Add basic properties
        $properties = [
            'id', 'category', 'parent', 'rank'
        ];

        foreach ($properties as $property) {
            if (isset($category[$property])) {
                $value = $category[$property];

                // Format parent category
                if ($property === 'parent' && !empty($value)) {
                    $parentCategory = $this->modx->getObject(\MODX\Revolution\modCategory::class, $value);
                    if ($parentCategory) {
                        $value .= ' (' . $parentCategory->get('category') . ')';
                    }
                }

                $table->addRow([$property, $value]);
            }
        }

        $table->render();
        return 0;
    }
}
