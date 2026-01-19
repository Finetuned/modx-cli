<?php

namespace MODX\CLI\Command\Template;

use MODX\CLI\Command\ProcessorCmd;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to get a MODX template
 */
class Get extends ProcessorCmd
{
    protected $processor = 'Element\Template\Get';
    protected $required = ['id'];

    protected $name = 'template:get';
    protected $description = 'Get a MODX template';

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
                'The ID of the template to get'
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
                    'message' => 'Template not found'
                ], JSON_PRETTY_PRINT));
                return 1;
            }
            $this->error('Template not found');
            return 1;
        }

        $template = $response['object'];

        // Check for both --json flag and --format=json
        if ($this->option('json') || $this->option('format') === 'json') {
            $this->output->writeln(json_encode($template, JSON_PRETTY_PRINT));
            return 0;
        }

        // Default to table format
        $table = new Table($this->output);
        $table->setHeaders(['Property', 'Value']);

        // Add basic properties
        $properties = [
            'id', 'templatename', 'description', 'category', 'locked', 'static', 'static_file', 'icon'
        ];

        foreach ($properties as $property) {
            if (isset($template[$property])) {
                $value = $template[$property];

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

        // Display template content separately
        if (isset($template['content']) && !empty($template['content'])) {
            $this->output->writeln("\n<info>Template Content:</info>");
            $this->output->writeln($template['content']);
        }
        return 0;
    }
}
