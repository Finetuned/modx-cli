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
    protected $required = array('id');

    protected $name = 'tv:get';
    protected $description = 'Get a MODX template variable';

    protected function getArguments()
    {
        return array(
            array(
                'id',
                InputArgument::REQUIRED,
                'The ID of the template variable to get'
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
                    'message' => 'Template variable not found'
                ], JSON_PRETTY_PRINT));
                return;
            }
            $this->error('Template variable not found');
            return;
        }

        $tv = $response['object'];

        // Check for both --json flag and --format=json
        if ($this->option('json') || $this->option('format') === 'json') {
            $this->output->writeln(json_encode($tv, JSON_PRETTY_PRINT));
            return;
        }

        // Default to table format
        $table = new Table($this->output);
        $table->setHeaders(array('Property', 'Value'));

        // Add basic properties
        $properties = array(
            'id', 'name', 'caption', 'description', 'category', 'type', 'default_text',
            'elements', 'rank', 'display', 'locked', 'static', 'static_file'
        );

        foreach ($properties as $property) {
            if (isset($tv[$property])) {
                $value = $tv[$property];

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

        // Display associated templates
        if (isset($tv['template_access'])) {
            $this->output->writeln("\n<info>Associated Templates:</info>");

            if (empty($tv['template_access'])) {
                $this->output->writeln("None (available to all templates)");
            } else {
                $templateTable = new Table($this->output);
                $templateTable->setHeaders(array('Template ID', 'Template Name'));

                foreach ($tv['template_access'] as $templateId) {
                    $template = $this->modx->getObject('modTemplate', $templateId);
                    $templateName = $template ? $template->get('templatename') : 'Unknown';
                    $templateTable->addRow(array($templateId, $templateName));
                }

                $templateTable->render();
            }
        }
    }
}
