<?php

namespace MODX\CLI\Command\TV;

use MODX\CLI\Command\ProcessorCmd;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to get a MODX template variable
 */
class Get extends ProcessorCmd
{
    protected $processor = 'Element\Tv\Get';
    protected $required = ['id'];

    protected $name = 'tv:get';
    protected $description = 'Get a MODX template variable';

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
                'The ID of the template variable to get'
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
                    'message' => 'Template variable not found'
                ], JSON_PRETTY_PRINT));
                return 1;
            }
            $this->error('Template variable not found');
            return 1;
        }

        $tv = $response['object'];

        // Check for both --json flag and --format=json
        if ($this->option('json') || $this->option('format') === 'json') {
            $this->output->writeln(json_encode($tv, JSON_PRETTY_PRINT));
            return 0;
        }

        // Default to table format
        $table = new Table($this->output);
        $table->setHeaders(['Property', 'Value']);

        // Add basic properties
        $properties = [
            'id', 'name', 'caption', 'description', 'category', 'type', 'default_text',
            'elements', 'rank', 'display', 'locked', 'static', 'static_file'
        ];

        foreach ($properties as $property) {
            if (isset($tv[$property])) {
                $value = $tv[$property];

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

        // Display associated templates
        if (isset($tv['template_access'])) {
            $this->output->writeln("\n<info>Associated Templates:</info>");

            if (empty($tv['template_access'])) {
                $this->output->writeln("None (available to all templates)");
            } else {
                $templateTable = new Table($this->output);
                $templateTable->setHeaders(['Template ID', 'Template Name']);

                foreach ($tv['template_access'] as $templateId) {
                    $template = $this->modx->getObject(\MODX\Revolution\modTemplate::class, $templateId);
                    $templateName = $template ? $template->get('templatename') : 'Unknown';
                    $templateTable->addRow([$templateId, $templateName]);
                }

                $templateTable->render();
            }
        }
        return 0;
    }
}
