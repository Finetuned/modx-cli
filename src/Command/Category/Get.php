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
    protected $required = array('id');

    protected $name = 'category:get';
    protected $description = 'Get a MODX category';

    protected function getArguments()
    {
        return array(
            array(
                'id',
                InputArgument::REQUIRED,
                'The ID of the category to get'
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
                $this->output->writeln(json_encode(['success' => false, 'message' => 'Category not found'], JSON_PRETTY_PRINT));
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
        $table->setHeaders(array('Property', 'Value'));

        // Add basic properties
        $properties = array(
            'id', 'category', 'parent', 'rank'
        );

        foreach ($properties as $property) {
            if (isset($category[$property])) {
                $value = $category[$property];

                // Format parent category
                if ($property === 'parent' && !empty($value)) {
                    $parentCategory = $this->modx->getObject('modCategory', $value);
                    if ($parentCategory) {
                        $value .= ' (' . $parentCategory->get('category') . ')';
                    }
                }

                $table->addRow(array($property, $value));
            }
        }

        $table->render();
        return 0;
    }
}
